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
        $this->assertSame(8, LaboratoryWorkflow::where('project_id', $project->id)->count());
        $this->assertSame(1, TestDocumentation::where('project_id', $project->id)->count());
        $this->assertSame(1, ReportApproval::where('project_id', $project->id)->where('status', 'valid')->count());
        $this->assertNotNull($project->document_hash);
        $this->assertNotNull($project->legalized_at);

        $this->get(route('public.report', $project->verification_code))
            ->assertOk()->assertSee('Pembangunan Gedung Laboratorium Beton')->assertSee('Observasi 2');
        $this->get(route('public.download', $project->verification_code))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }
}
