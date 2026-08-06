<?php

namespace Tests\Feature;

use App\Models\AggregateTestObservation;
use App\Models\AggregateTestRun;
use App\Models\Project;
use App\Models\User;
use App\Models\WaterTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AggregateObservationPersistenceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['username' => 'observer-tester']);
        $this->project = Project::create([
            'number' => 'OBS-001',
            'name' => 'Proyek Observasi',
            'status' => 'aktif',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_multiple_observations_can_be_loaded_edited_added_and_deleted_independently(): void
    {
        $this->actingAs($this->user)
            ->post(route('aggregate-tests.store', ['fine', 'moisture']), $this->payload([
                ['container' => 10, 'wet_container' => 110, 'dry_container' => 100],
                ['container' => 20, 'wet_container' => 240, 'dry_container' => 220],
                ['container' => 30, 'wet_container' => 390, 'dry_container' => 330],
            ]))
            ->assertOk();

        $run = AggregateTestRun::sole();
        $this->assertSame(3, $run->observationRecords()->count());
        $this->assertSame([110, 240, 390], $run->observationRecords()->get()->pluck('data.wet_container')->all());

        $this->get(route('aggregate-tests.create', ['fine', 'moisture']))
            ->assertOk()
            ->assertViewHas('savedRuns', fn ($runs) => count($runs->get($this->project->id.'-any')['observations']) === 3
                && data_get($runs->get($this->project->id.'-any'), 'observations.1.wet_container') === 240);

        $records = $run->observationRecords()->get();
        $this->post(route('aggregate-tests.store', ['fine', 'moisture']), $this->payload([
            ['id' => $records[0]->id, 'container' => 10, 'wet_container' => 110, 'dry_container' => 100],
            ['id' => $records[1]->id, 'container' => 20, 'wet_container' => 260, 'dry_container' => 220],
            ['id' => $records[2]->id, 'container' => 30, 'wet_container' => 390, 'dry_container' => 330],
        ]))->assertOk();

        $this->assertSame([110, 260, 390], $run->observationRecords()->get()->pluck('data.wet_container')->all());

        $records = $run->observationRecords()->get();
        $this->post(route('aggregate-tests.store', ['fine', 'moisture']), $this->payload([
            ['id' => $records[0]->id, 'container' => 10, 'wet_container' => 110, 'dry_container' => 100],
            ['id' => $records[1]->id, 'container' => 20, 'wet_container' => 260, 'dry_container' => 220],
            ['id' => $records[2]->id, 'container' => 30, 'wet_container' => 390, 'dry_container' => 330],
            ['container' => 40, 'wet_container' => 500, 'dry_container' => 440],
        ]))->assertOk();

        $this->assertSame(4, $run->observationRecords()->count());
        $records = $run->observationRecords()->get();
        $deletedId = $records[1]->id;
        WaterTest::create([
            'project_id' => $this->project->id, 'test_number' => 'WAT-SAFE-01', 'sample_number' => 'AIR-SAFE',
            'tested_at' => '2026-08-01', 'technician' => 'Petugas Air', 'water_source' => 'Sumur',
            'ph' => 7, 'created_by' => $this->user->id,
        ]);

        $this->deleteJson(route('aggregate-tests.observations.destroy', [
            $this->project, $run, $records[1],
        ]))->assertOk()->assertJson(['message' => 'Observasi berhasil dihapus.']);

        $run->refresh();
        $this->assertDatabaseMissing('aggregate_test_observations', ['id' => $deletedId]);
        $this->assertSame([110, 390, 500], $run->observationRecords()->get()->pluck('data.wet_container')->all());
        $this->assertSame([1, 2, 3], $run->observationRecords()->pluck('observation_number')->all());
        $this->assertCount(3, $run->observations);
        $this->assertDatabaseHas('projects', ['id' => $this->project->id]);
        $this->assertDatabaseHas('water_tests', ['project_id' => $this->project->id, 'test_number' => 'WAT-SAFE-01']);
    }

    public function test_other_project_cannot_update_or_delete_an_observation(): void
    {
        $this->actingAs($this->user)->post(route('aggregate-tests.store', ['fine', 'moisture']), $this->payload([
            ['container' => 10, 'wet_container' => 110, 'dry_container' => 100],
            ['container' => 20, 'wet_container' => 240, 'dry_container' => 220],
        ]))->assertOk();

        $run = AggregateTestRun::sole();
        $observation = $run->observationRecords()->first();
        $otherProject = Project::create([
            'number' => 'OBS-002', 'name' => 'Proyek Lain', 'status' => 'aktif', 'created_by' => $this->user->id,
        ]);

        $foreignPayload = $this->payload([
            ['id' => $observation->id, 'container' => 10, 'wet_container' => 999, 'dry_container' => 100],
        ]);
        $foreignPayload['project_id'] = $otherProject->id;

        $this->post(route('aggregate-tests.store', ['fine', 'moisture']), $foreignPayload)->assertNotFound();
        $this->deleteJson(route('aggregate-tests.observations.destroy', [$otherProject, $run, $observation]))->assertNotFound();
        $this->assertSame(110, AggregateTestObservation::findOrFail($observation->id)->data['wet_container']);
        $this->assertDatabaseCount('aggregate_test_runs', 1);
    }

    public function test_failed_calculation_rolls_back_all_observation_updates(): void
    {
        $this->actingAs($this->user)->post(route('aggregate-tests.store', ['fine', 'moisture']), $this->payload([
            ['container' => 10, 'wet_container' => 110, 'dry_container' => 100],
            ['container' => 20, 'wet_container' => 240, 'dry_container' => 220],
        ]))->assertOk();

        $run = AggregateTestRun::sole();
        $records = $run->observationRecords()->get();

        $this->from(route('aggregate-tests.create', ['fine', 'moisture']))
            ->post(route('aggregate-tests.store', ['fine', 'moisture']), $this->payload([
                ['id' => $records[0]->id, 'container' => 10, 'wet_container' => 999, 'dry_container' => 100],
                ['id' => $records[1]->id, 'container' => 50, 'wet_container' => 60, 'dry_container' => 40],
            ]))
            ->assertRedirect()
            ->assertSessionHasErrors('observations');

        $this->assertSame([110, 240], $run->observationRecords()->get()->pluck('data.wet_container')->all());
    }

    public function test_incomplete_fine_aggregate_worksheet_can_be_saved_and_reopened_as_draft(): void
    {
        $runs = [
            'moisture' => ['observations' => [['container' => 10, 'wet_container' => null, 'dry_container' => null]]],
            'silt' => ['observations' => [['dry_before' => null, 'dry_after' => null]]],
            'specific-gravity' => ['observations' => [['oven_dry' => null, 'pyc_water' => null, 'pyc_sample_water' => null, 'ssd' => null]]],
            'bulk-density' => ['observations' => [['container' => null, 'full_container' => null, 'volume' => null, 'specific_gravity' => null]]],
            'sieve' => ['observations' => [[
                'sample_mass' => null, 'r095' => 0, 'r475' => 0, 'r236' => 0, 'r118' => 0,
                'r060' => 0, 'r030' => 0, 'r015' => 0, 'pan' => 0,
            ]]],
        ];

        $this->actingAs($this->user)->post(route('aggregate-tests.worksheet.store', 'fine'), [
            'project_id' => $this->project->id,
            'sample_number' => 'DRAF-PASIR-01',
            'tested_at' => '2026-08-01',
            'technician' => 'Teknisi Draf',
            'runs' => $runs,
        ])->assertRedirect(route('material-results.project', $this->project));

        $this->assertSame(5, AggregateTestRun::count());
        $this->assertSame(5, AggregateTestObservation::count());
        $this->assertTrue(AggregateTestRun::all()->every(fn ($run) => $run->results['valid'] === false));

        $this->get(route('aggregate-tests.worksheet', 'fine'))
            ->assertOk()
            ->assertViewHas('savedRuns', fn ($saved) => data_get($saved->get($this->project->id.'-any-moisture'), 'observations.0.container') === 10);
    }

    private function payload(array $observations): array
    {
        return [
            'project_id' => $this->project->id,
            'sample_number' => 'S-OBS-01',
            'tested_at' => '2026-08-01',
            'technician' => 'Teknisi Uji',
            'observations' => $observations,
            'notes' => 'Catatan observasi berbeda',
        ];
    }
}
