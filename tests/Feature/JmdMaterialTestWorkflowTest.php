<?php

namespace Tests\Feature;

use App\Models\Jmd\MoistureTest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JmdMaterialTestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_create_recalculate_edit_and_remove_selected_observation(): void
    {
        [$user, $project] = $this->identity('JMD-WORK-1');
        $payload = $this->payload([
            ['container_mass' => 20, 'wet_container_mass' => 120, 'dry_container_mass' => 110],
            ['container_mass' => 21, 'wet_container_mass' => 121, 'dry_container_mass' => 111],
            ['container_mass' => 22, 'wet_container_mass' => 122, 'dry_container_mass' => 112],
        ]);

        $this->actingAs($user)->post(route('jmd.material-tests.moisture.store', $project), $payload)
            ->assertRedirect();

        $test = MoistureTest::with('items')->sole();
        $this->assertCount(3, $test->items);
        $this->assertSame('completed', $test->status);
        $this->assertEqualsWithDelta(11.111111, data_get($test->result_snapshot, 'raw.statistics.average'), 0.000001);
        $this->assertStringStartsWith('JMD-KA-', $test->test_number);

        $kept = $test->items->take(2)->map(fn ($item) => [
            'id' => $item->id,
            'container_mass' => (float) $item->container_mass,
            'wet_container_mass' => (float) $item->wet_container_mass,
            'dry_container_mass' => (float) $item->dry_container_mass,
        ])->all();
        $payload = $this->payload($kept) + ['test_id' => $test->id];
        $payload['technician'] = 'Teknisi Revisi';

        $this->post(route('jmd.material-tests.moisture.store', $project), $payload)->assertRedirect();

        $this->assertDatabaseCount('moisture_tests', 1);
        $this->assertDatabaseCount('moisture_test_items', 2);
        $this->assertSame('Teknisi Revisi', $test->fresh()->technician);
        $this->assertDatabaseHas('audit_logs', ['module' => 'jmd-material-tests', 'action' => 'update', 'record_id' => $test->id]);
    }

    public function test_test_id_cannot_cross_project_boundary(): void
    {
        [$user, $first] = $this->identity('JMD-OWN-1');
        [, $second] = $this->identity('JMD-OWN-2', $user);
        $this->actingAs($user)->post(route('jmd.material-tests.moisture.store', $first), $this->payload([
            ['container_mass' => 20, 'wet_container_mass' => 120, 'dry_container_mass' => 110],
            ['container_mass' => 20, 'wet_container_mass' => 130, 'dry_container_mass' => 115],
        ]))->assertRedirect();
        $test = MoistureTest::sole();

        $payload = $this->payload([
            ['container_mass' => 20, 'wet_container_mass' => 125, 'dry_container_mass' => 112],
            ['container_mass' => 20, 'wet_container_mass' => 135, 'dry_container_mass' => 118],
        ]) + ['test_id' => $test->id];
        $this->post(route('jmd.material-tests.moisture.store', $second), $payload)->assertNotFound();

        $this->assertSame($first->id, $test->fresh()->project_id);
        $this->assertDatabaseCount('moisture_tests', 1);
    }

    public function test_all_other_material_test_modules_persist_calculation_snapshots(): void
    {
        [$user, $project] = $this->identity('JMD-ALL-1');
        $base = ['tested_at' => '2026-08-07', 'technician' => 'Teknisi JMD', 'standard_source' => 'Acuan laboratorium'];
        $cases = [
            'silt' => $base + ['aggregate_type' => 'fine', 'limit_percent' => 5, 'observations' => [
                ['container_mass' => 20, 'before_wash_container_mass' => 120, 'after_wash_container_mass' => 117],
                ['container_mass' => 20, 'before_wash_container_mass' => 120, 'after_wash_container_mass' => 116],
            ]],
            'fine-specific-gravity' => $base + ['observations' => [
                ['pycnometer_mass' => 100, 'ssd_sample_mass' => 100, 'pycnometer_sample_water_mass' => 550, 'pycnometer_water_mass' => 500, 'oven_dry_sample_mass' => 98],
                ['pycnometer_mass' => 100, 'ssd_sample_mass' => 102, 'pycnometer_sample_water_mass' => 552, 'pycnometer_water_mass' => 500, 'oven_dry_sample_mass' => 100],
            ]],
            'coarse-specific-gravity' => $base + ['observations' => [
                ['ssd_air_mass' => 100, 'submerged_mass' => 60, 'oven_dry_mass' => 98],
                ['ssd_air_mass' => 102, 'submerged_mass' => 61, 'oven_dry_mass' => 100],
            ]],
            'bulk-density' => $base + ['material_type' => 'fine', 'mass_unit' => 'g', 'observations' => [
                ['condition' => 'loose', 'mould_volume_cm3' => 1000, 'mould_mass' => 2000, 'filled_mould_mass' => 3500],
                ['condition' => 'rodded', 'mould_volume_cm3' => 1000, 'mould_mass' => 2000, 'filled_mould_mass' => 3650],
            ]],
            'cement-specific-gravity' => $base + ['observations' => [
                ['bottle_kerosene_mass' => 500, 'bottle_cement_kerosene_mass' => 600, 'initial_reading_ml' => 10, 'final_reading_ml' => 42, 'water_density' => 0.998],
                ['bottle_kerosene_mass' => 501, 'bottle_cement_kerosene_mass' => 601, 'initial_reading_ml' => 10, 'final_reading_ml' => 42.5, 'water_density' => 0.998],
            ]],
            'sieve' => $base + ['aggregate_type' => 'fine', 'initial_sample_mass' => 1000, 'loss_tolerance_percent' => 1, 'observations' => [
                ['sieve_label' => 'No. 4', 'sieve_size_mm' => 4.75, 'is_pan' => false, 'retained_mass' => 100, 'lower_limit_percent' => 80, 'upper_limit_percent' => 100],
                ['sieve_label' => 'Pan', 'is_pan' => true, 'retained_mass' => 900],
            ]],
            'abrasion' => $base + ['limit_percent' => 40, 'steel_ball_count' => 11, 'revolution_count' => 500, 'observations' => [
                ['passing_sieve_mm' => 19, 'retained_sieve_mm' => 12.5, 'initial_mass' => 5000, 'retained_no12_mass' => 4000],
                ['passing_sieve_mm' => 12.5, 'retained_sieve_mm' => 9.5, 'initial_mass' => 2500, 'retained_no12_mass' => 2050],
            ]],
        ];

        $this->actingAs($user);
        foreach ($cases as $module => $payload) {
            $this->post(route('jmd.material-tests.'.$module.'.store', $project), $payload)
                ->assertSessionHasNoErrors()->assertRedirect();
        }

        foreach (['silt_tests', 'fine_aggregate_sg_tests', 'coarse_aggregate_sg_tests', 'bulk_density_tests', 'cement_sg_tests', 'sieve_tests', 'abrasion_tests'] as $table) {
            $this->assertDatabaseCount($table, 1);
        }
    }

    public function test_reader_can_view_but_cannot_store_and_locked_project_cannot_be_changed(): void
    {
        [$editor, $project] = $this->identity('JMD-LOCK-1');
        $reader = User::factory()->create(['username' => 'jmd-reader', 'role' => 'teknisi', 'access_level' => 'read']);

        $this->actingAs($reader)->get(route('jmd.material-tests.index', $project))->assertOk();
        $this->post(route('jmd.material-tests.moisture.store', $project), $this->payload([[], []]))->assertForbidden();

        $project->update(['locked_at' => now()]);
        $this->actingAs($editor)->post(route('jmd.material-tests.moisture.store', $project), $this->payload([[], []]))->assertStatus(423);
    }

    public function test_invalid_formula_input_returns_validation_error_without_partial_record(): void
    {
        [$user, $project] = $this->identity('JMD-INVALID-1');
        $payload = [
            'tested_at' => '2026-08-07', 'technician' => 'Teknisi JMD', 'standard_source' => 'SNI 1969:2016',
            'observations' => [
                ['ssd_air_mass' => 100, 'submerged_mass' => 90, 'oven_dry_mass' => 80],
                ['ssd_air_mass' => 110, 'submerged_mass' => 95, 'oven_dry_mass' => 85],
            ],
        ];

        $this->actingAs($user)->from(route('jmd.material-tests.form', [$project, 'coarse-specific-gravity']))
            ->post(route('jmd.material-tests.coarse-specific-gravity.store', $project), $payload)
            ->assertRedirect()->assertSessionHasErrors('observations');

        $this->assertDatabaseCount('coarse_aggregate_sg_tests', 0);
        $this->assertDatabaseCount('coarse_aggregate_sg_items', 0);
    }

    private function payload(array $observations): array
    {
        return [
            'aggregate_type' => 'fine', 'tested_at' => '2026-08-07',
            'technician' => 'Teknisi JMD', 'standard_source' => 'SNI 1971:2011',
            'observations' => $observations,
        ];
    }

    private function identity(string $number, ?User $user = null): array
    {
        $user ??= User::factory()->create(['username' => strtolower($number), 'role' => 'teknisi', 'access_level' => 'edit']);
        $project = Project::create(['number' => $number, 'name' => 'Proyek '.$number, 'status' => 'aktif', 'created_by' => $user->id]);

        return [$user, $project];
    }
}
