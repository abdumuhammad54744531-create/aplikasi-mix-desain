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
            ->assertSee('Komposisi Campuran Percobaan untuk Benda Uji Silinder')
            ->assertSee('150,000 mm')
            ->assertSee('300,000 mm')
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
        $this->assertEqualsWithDelta(pi()/4*.15**2*.30*3*1000, $mix->input_data['trial_volume_liter'], .000001);
        $this->actingAs($user)->get(route('mix-design-2012-combined.create', ['project' => $project->id]))
            ->assertOk()
            ->assertSee('value="39.1"', false);
    }

    public function test_manual_slump_limits_are_validated_saved_and_restored(): void
    {
        $user = User::factory()->create(['username' => 'slump-editor', 'role' => 'administrator', 'access_level' => 'edit']);
        $this->seed(CompleteJmdReportDemoSeeder::class);
        $project = Project::where('number', 'DEMO-JMD-LENGKAP-001')->sole();
        $project->update(['locked_at' => null, 'status' => 'aktif']);
        $mix = LaboratoryWorkflow::where('project_id', $project->id)->where('type', 'mix-design-2012')->sole();
        $input = $mix->input_data;
        $input['construction_type'] = 6;
        $input['slump_min'] = 110;
        $input['slump_max'] = 160;
        $input['slump_design'] = 140;

        $this->actingAs($user)->post(route('mix-design-2012.store'), [
            'workflow_id' => $mix->id,
            'project_id' => $project->id,
            'work_date' => $mix->work_date->format('Y-m-d'),
            'data' => $input,
            'notes' => 'Batas slump kebutuhan khusus.',
        ])->assertSessionDoesntHaveErrors();

        $mix->refresh();
        $this->assertSame(6, $mix->input_data['construction_type']);
        $this->assertSame(110, $mix->input_data['slump_min']);
        $this->assertSame(160, $mix->input_data['slump_max']);
        $this->assertSame(140, $mix->input_data['slump_design']);

        $this->actingAs($user)->get(route('mix-design-2012.create', ['project' => $project->id]))
            ->assertOk()
            ->assertSee('Lainnya / kebutuhan khusus')
            ->assertSee('value="6" data-manual="1" checked', false)
            ->assertSee('name="data[slump_min]" data-key="slump_min" value="110"', false)
            ->assertSee('name="data[slump_max]" data-key="slump_max" value="160"', false);

        $input['slump_design'] = 170;
        $this->actingAs($user)->from(route('mix-design-2012.create', ['project' => $project->id]))
            ->post(route('mix-design-2012.store'), [
                'workflow_id' => $mix->id,
                'project_id' => $project->id,
                'work_date' => $mix->work_date->format('Y-m-d'),
                'data' => $input,
            ])->assertSessionHasErrors('data.slump_design');
    }
}
