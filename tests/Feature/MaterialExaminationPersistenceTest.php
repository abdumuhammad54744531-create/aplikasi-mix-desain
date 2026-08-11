<?php

namespace Tests\Feature;

use App\Models\CementTest;
use App\Models\CoarseAggregateTest;
use App\Models\FineAggregateTest;
use App\Models\Project;
use App\Models\User;
use App\Models\WaterTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialExaminationPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_water_test_is_reloaded_and_only_the_selected_record_is_updated(): void
    {
        [$user, $project] = $this->identity();
        $payload = [
            'project_id' => $project->id,
            'sample_number' => 'AIR-01',
            'received_at' => '',
            'tested_at' => '2026-08-01',
            'technician' => 'Petugas Air',
            'water_source' => 'Sumur bor',
            'sampling_location' => 'Lokasi A',
            'color' => 'Jernih',
            'odor' => '',
            'ph' => 7.2,
            'silt_content' => 0,
            'organic_content' => 0.00,
            'chloride' => 12.5,
            'notes' => 'Catatan pemeriksaan air yang panjang dan tetap tersimpan.',
        ];

        $this->actingAs($user)->post(route('material-tests.store', 'water'), $payload)
            ->assertRedirect(route('material-tests.index'));

        $water = WaterTest::sole();
        $this->assertSame($project->id, $water->project_id);
        $this->assertEquals(0.0, (float) $water->silt_content);
        $this->assertNull($water->received_at);

        $this->get(route('material-tests.create', 'water'))
            ->assertOk()
            ->assertViewHas('savedTests', function ($saved) use ($project) {
                $record = $saved->get($project->id.'-any');
                return $record['water_source'] === 'Sumur bor'
                    && (float) $record['ph'] === 7.2
                    && (float) $record['silt_content'] === 0.0
                    && $record['notes'] === 'Catatan pemeriksaan air yang panjang dan tetap tersimpan.';
            });

        $payload['test_id'] = $water->id;
        $payload['ph'] = 6.85;
        $this->post(route('material-tests.store', 'water'), $payload)->assertRedirect();

        $this->assertDatabaseCount('water_tests', 1);
        $water->refresh();
        $this->assertEquals(6.85, (float) $water->ph);
        $this->assertEquals(12.5, (float) $water->chloride);

        $otherProject = Project::create([
            'number' => 'MAT-002', 'name' => 'Proyek Material Lain', 'status' => 'aktif', 'created_by' => $user->id,
        ]);
        $payload['project_id'] = $otherProject->id;
        $payload['ph'] = 9;
        $this->post(route('material-tests.store', 'water'), $payload)->assertNotFound();
        $this->assertEquals(6.85, (float) $water->fresh()->ph);
        $this->assertDatabaseCount('water_tests', 1);
    }

    public function test_every_available_material_examination_uses_record_ids_for_reload_and_update(): void
    {
        [$user, $project] = $this->identity();
        $cases = [
            'cement' => [CementTest::class, ['cement_type' => 'PCC', 'specific_gravity' => 3.15], 'specific_gravity', 3.10],
            'fine-aggregate' => [FineAggregateTest::class, [
                'bulk_specific_gravity_dry' => 2.55, 'specific_gravity_ssd' => 2.61,
                'absorption' => 2.30, 'field_moisture' => 0,
            ], 'absorption', 2.10],
            'coarse-aggregate' => [CoarseAggregateTest::class, [
                'aggregate_type' => 'Batu pecah', 'nominal_maximum_size' => 20,
                'bulk_specific_gravity_dry' => 2.60, 'specific_gravity_ssd' => 2.65,
                'absorption' => 1.25, 'field_moisture' => 0,
            ], 'absorption', 1.10],
        ];

        $this->actingAs($user);
        foreach ($cases as $type => [$model, $fields, $changedField, $changedValue]) {
            $payload = array_merge([
                'project_id' => $project->id,
                'sample_number' => strtoupper($type).'-01',
                'tested_at' => '2026-08-01',
                'technician' => 'Petugas Material',
            ], $fields);

            $this->post(route('material-tests.store', $type), $payload)->assertRedirect();
            $record = $model::sole();

            $this->get(route('material-tests.create', $type))
                ->assertOk()
                ->assertViewHas('savedTests', fn ($saved) => (int) $saved->get($project->id.'-any')['id'] === $record->id);

            $payload['test_id'] = $record->id;
            $payload[$changedField] = $changedValue;
            $this->post(route('material-tests.store', $type), $payload)->assertRedirect();

            $this->assertSame(1, $model::count(), $type.' membuat record ganda saat diedit.');
            $this->assertEquals($changedValue, (float) $record->fresh()->{$changedField});
        }
    }

    public function test_only_specific_gravity_is_required_for_cement_characteristics(): void
    {
        [$user, $project] = $this->identity();

        $this->actingAs($user)->post(route('material-tests.store', 'cement'), [
            'project_id' => $project->id,
            'sample_number' => 'SEMEN-MINIMAL-01',
            'tested_at' => '2026-08-11',
            'technician' => 'Petugas Semen',
            'specific_gravity' => 3.15,
        ])->assertRedirect(route('material-tests.index'));

        $cement = CementTest::sole();
        $this->assertSame(3.15, (float) $cement->specific_gravity);
        $this->assertNull($cement->cement_type);
        $this->assertNull($cement->fineness);
    }

    public function test_only_source_and_location_are_required_for_water_characteristics(): void
    {
        [$user, $project] = $this->identity();
        $payload = [
            'project_id' => $project->id,
            'sample_number' => 'AIR-MINIMAL-01',
            'tested_at' => '2026-08-11',
            'technician' => 'Petugas Air',
            'water_source' => 'Sumur bor',
            'sampling_location' => 'Bak penampung laboratorium',
        ];

        $this->actingAs($user)->post(route('material-tests.store', 'water'), $payload)
            ->assertRedirect(route('material-tests.index'));

        $water = WaterTest::sole();
        $this->assertSame('Sumur bor', $water->water_source);
        $this->assertSame('Bak penampung laboratorium', $water->sampling_location);
        $this->assertNull($water->ph);

        unset($payload['sampling_location']);
        $this->post(route('material-tests.store', 'water'), $payload)
            ->assertSessionHasErrors('sampling_location');
    }

    private function identity(): array
    {
        $user = User::factory()->create(['username' => 'material-tester']);
        $project = Project::create([
            'number' => 'MAT-001', 'name' => 'Proyek Material', 'status' => 'aktif', 'created_by' => $user->id,
        ]);

        return [$user, $project];
    }
}
