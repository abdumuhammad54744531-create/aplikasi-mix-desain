<?php

namespace Tests\Feature;

use App\Models\LaboratoryWorkflow;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectMixDesignReportSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_simplified_project_form_generates_number_and_enables_both_report_types(): void
    {
        $user = User::factory()->create(['username' => 'teknisi-pilihan', 'role' => 'teknisi', 'access_level' => 'edit']);

        $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Proyek Pilihan Laporan',
        ])->assertSessionDoesntHaveErrors();

        $project=Project::sole();
        $this->assertStringStartsWith('PRJ-', $project->number);
        $this->assertTrue($project->report_include_mix_design_2012);
        $this->assertTrue($project->report_include_mix_design_2012_combined);
    }

    public function test_report_only_contains_mix_design_types_selected_on_project(): void
    {
        $user = User::factory()->create(['username' => 'teknisi-laporan', 'role' => 'teknisi', 'access_level' => 'edit']);
        $project = Project::create([
            'number' => 'PRJ-LAPORAN-001',
            'name' => 'Proyek Penyaringan Laporan',
            'status' => 'aktif',
            'report_include_mix_design_2012' => true,
            'report_include_mix_design_2012_combined' => false,
        ]);

        $this->createMix($project, 'mix-design-2012', 'MD-STANDAR-001');
        $this->createMix($project, 'mix-design-2012-combined', 'MD-GABUNGAN-001');

        $this->actingAs($user)->get(route('workflow.report.final', $project))
            ->assertOk()
            ->assertSee('MD-STANDAR-001')
            ->assertDontSee('MD-GABUNGAN-001')
            ->assertSee('<td class="center mix-active">216</td>', false)
            ->assertDontSee('<td class="center mix-active">202</td>', false);

        $project->update(['report_include_mix_design_2012_combined' => true]);
        $this->actingAs($user)->get(route('workflow.report.final', $project->fresh()))
            ->assertOk()
            ->assertSee('MD-STANDAR-001')
            ->assertSee('MD-GABUNGAN-001')
            ->assertSeeInOrder([
                'PERHITUNGAN DESAIN CAMPURAN 2012',
                'PERHITUNGAN DESAIN CAMPURAN 2012 (GRADASI GABUNGAN)',
            ]);

        $project->update([
            'report_include_mix_design_2012' => false,
            'report_include_mix_design_2012_combined' => true,
        ]);
        $this->actingAs($user)->get(route('workflow.report.final', $project->fresh()))
            ->assertOk()
            ->assertDontSee('MD-STANDAR-001')
            ->assertSee('MD-GABUNGAN-001');
    }

    public function test_user_can_select_available_mix_design_method_for_unlocked_report(): void
    {
        $user = User::factory()->create(['username' => 'teknisi-metode', 'role' => 'teknisi', 'access_level' => 'edit']);
        $project = Project::create([
            'number' => 'PRJ-METODE-001',
            'name' => 'Proyek Pilihan Metode',
            'status' => 'aktif',
            'report_include_mix_design_2012' => true,
            'report_include_mix_design_2012_combined' => true,
        ]);
        $this->createMix($project, 'mix-design-2012', 'MD-STANDAR-002');
        $this->createMix($project, 'mix-design-2012-combined', 'MD-GABUNGAN-002');

        $this->actingAs($user)->patch(route('workflow.report.mix-design-selection', $project), [
            'report_mix_design_method' => 'mix-design-2012-combined',
        ])->assertSessionDoesntHaveErrors();

        $project->refresh();
        $this->assertFalse($project->report_include_mix_design_2012);
        $this->assertTrue($project->report_include_mix_design_2012_combined);
        $this->actingAs($user)->get(route('workflow.report.project', $project))
            ->assertOk()
            ->assertSee('Metode desain campuran yang dimasukkan ke laporan')
            ->assertSee('Hasil desain tersedia');

        $project->update(['locked_at' => now()]);
        $this->actingAs($user)->patch(route('workflow.report.mix-design-selection', $project), [
            'report_mix_design_method' => 'mix-design-2012',
        ])->assertStatus(423);
    }

    public function test_report_uses_the_most_recently_updated_mix_design_result(): void
    {
        $user = User::factory()->create(['username' => 'teknisi-versi', 'role' => 'teknisi', 'access_level' => 'edit']);
        $project = Project::create([
            'number' => 'PRJ-VERSI-001',
            'name' => 'Proyek Versi Perhitungan',
            'status' => 'aktif',
            'report_include_mix_design_2012' => false,
            'report_include_mix_design_2012_combined' => true,
        ]);
        $revised = $this->createMix($project, 'mix-design-2012-combined', 'MD-GABUNGAN-REVISI');
        $this->createMix($project, 'mix-design-2012-combined', 'MD-GABUNGAN-LAMA');
        $revised->update(['updated_at' => now()->addMinute()]);

        $this->actingAs($user)->get(route('workflow.report.final', $project))
            ->assertOk()
            ->assertSee('MD-GABUNGAN-REVISI')
            ->assertDontSee('MD-GABUNGAN-LAMA');
    }

    private function createMix(Project $project, string $type, string $number): LaboratoryWorkflow
    {
        return LaboratoryWorkflow::create([
            'project_id' => $project->id,
            'type' => $type,
            'number' => $number,
            'work_date' => now()->toDateString(),
            'input_data' => ['fc' => 25, 'water' => 216, 'slump_min' => 150, 'slump_max' => 175, 'slump_design' => 160, 'max_size' => 19, 'air_entrained' => 0],
            'result_data' => ['cement' => 400, 'fine_ssd' => 700, 'coarse_ssd' => 1000],
            'status' => 'disetujui',
        ]);
    }
}
