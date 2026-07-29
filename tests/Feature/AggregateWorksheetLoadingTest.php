<?php

namespace Tests\Feature;

use App\Models\AggregateTestRun;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AggregateWorksheetLoadingTest extends TestCase
{
    use RefreshDatabase;

    public function test_latest_project_test_is_available_even_without_material_source(): void
    {
        $user = User::factory()->create(['username' => 'worksheet-user']);
        $project = Project::create([
            'number' => 'PASIR-001',
            'name' => 'Proyek Pemeriksaan Pasir',
            'status' => 'aktif',
            'created_by' => $user->id,
        ]);
        AggregateTestRun::create([
            'project_id' => $project->id,
            'material_source_id' => null,
            'test_number' => 'FMOI-001',
            'aggregate_type' => 'fine',
            'test_type' => 'moisture',
            'sample_number' => 'S-001',
            'tested_at' => now(),
            'technician' => 'Teknisi',
            'observations' => [['container' => 0, 'wet_container' => 1000, 'dry_container' => 950]],
            'results' => ['averages' => ['moisture' => 5.263]],
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('aggregate-tests.worksheet', 'fine'));

        $response->assertOk()
            ->assertViewHas('savedRuns', fn ($runs) => $runs->has($project->id.'-any-moisture'))
            ->assertSee('Hasil Rata-rata')
            ->assertSee('Otomatis dari total massa tertahan.');
    }
}
