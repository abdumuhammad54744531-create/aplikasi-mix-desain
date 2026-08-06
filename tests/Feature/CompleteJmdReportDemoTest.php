<?php

namespace Tests\Feature;

use App\Models\AggregateTestRun;
use App\Models\LaboratoryWorkflow;
use App\Models\Project;
use App\Models\ReportApproval;
use App\Models\TestDocumentation;
use App\Models\User;
use Database\Seeders\CompleteJmdReportDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteJmdReportDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_builds_complete_approved_report_with_two_observations(): void
    {
        User::factory()->create(['username' => 'demo-admin', 'role' => 'administrator', 'access_level' => 'edit']);
        $this->seed(CompleteJmdReportDemoSeeder::class);
        $this->seed(CompleteJmdReportDemoSeeder::class);

        $project = Project::where('number', 'DEMO-JMD-LENGKAP-001')->sole();
        $runs = AggregateTestRun::where('project_id', $project->id)->get();
        $this->assertCount(11, $runs);
        $this->assertTrue($runs->every(fn ($run) => count($run->observations) === 2));
        $this->assertTrue($runs->every(fn ($run) => $run->status === 'disetujui'));
        $this->assertSame(9, LaboratoryWorkflow::where('project_id', $project->id)->count());
        $this->assertTrue(LaboratoryWorkflow::where('project_id', $project->id)->where('type', 'mix-design-2012-combined')->exists());
        $this->assertSame(1, TestDocumentation::where('project_id', $project->id)->count());
        $this->assertSame(1, ReportApproval::where('project_id', $project->id)->where('status', 'valid')->count());
        $this->assertNotNull($project->document_hash);
        $this->assertNotNull($project->legalized_at);

        $this->get(route('public.report', $project->verification_code))
            ->assertOk()
            ->assertSee('Pembangunan Gedung Laboratorium Beton')
            ->assertSee('Observasi 2')
            ->assertSee('DEMO-COARSE-LOSANGELES')
            ->assertDontSee('Data pengujian belum tersedia pada proyek ini');
        $this->get(route('public.download', $project->verification_code))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_saved_combined_gradation_snapshot_is_preserved_when_project_is_reopened(): void
    {
        $user = User::factory()->create(['username' => 'combined-editor', 'role' => 'administrator', 'access_level' => 'edit']);
        $this->seed(CompleteJmdReportDemoSeeder::class);
        $project = Project::where('number', 'DEMO-JMD-LENGKAP-001')->sole();
        $project->update(['locked_at' => null, 'status' => 'aktif']);
        $mix = LaboratoryWorkflow::where('project_id', $project->id)->where('type', 'mix-design-2012-combined')->sole();
        $input = $mix->input_data;
        $input['combined_fine_percent'] = 39.1;
        $input['combined_coarse_percent'] = 60.9;

        $this->actingAs($user)->post(route('mix-design-2012-combined.store'), [
            'workflow_id' => $mix->id,
            'project_id' => $project->id,
            'work_date' => $mix->work_date->format('Y-m-d'),
            'data' => $input,
            'notes' => 'Snapshot optimum 39,1% pasir dan 60,9% kerikil.',
        ])->assertSessionDoesntHaveErrors();

        $mix->refresh();
        $this->assertSame(39.1, $mix->input_data['combined_fine_percent']);
        $this->assertSame(60.9, $mix->input_data['combined_coarse_percent']);
        $this->assertSame(39.1, $mix->result_data['combined_fine_percent']);
        $this->assertSame(60.9, $mix->result_data['combined_coarse_percent']);
        $this->actingAs($user)->get(route('mix-design-2012-combined.create', ['project' => $project->id]))
            ->assertOk()
            ->assertSee('value="39.1"', false);
    }
}
