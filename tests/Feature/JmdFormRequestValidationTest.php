<?php

namespace Tests\Feature;

use App\Http\Requests\Jmd\CalculateMixDesignRequest;
use App\Http\Requests\Jmd\StoreCompressiveStrengthTestRequest;
use App\Http\Requests\Jmd\StoreMoistureTestRequest;
use App\Http\Requests\Jmd\StoreTrialMixRequest;
use App\Models\Jmd\DesignCriterion;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class JmdFormRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_moisture_request_requires_two_valid_observations(): void
    {
        $validator = Validator::make([
            'project_id' => 1,
            'test_number' => 'MC-1',
            'aggregate_type' => 'fine',
            'tested_at' => '2026-08-06',
            'technician' => 'Teknisi',
            'observations' => [[
                'container_mass' => 20,
                'wet_container_mass' => 19,
                'dry_container_mass' => 18,
            ]],
        ], (new StoreMoistureTestRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('observations', $validator->errors()->toArray());
        $this->assertArrayHasKey('observations.0.wet_container_mass', $validator->errors()->toArray());
    }

    public function test_mix_design_override_requires_reason_and_standard_sources(): void
    {
        $request = new CalculateMixDesignRequest;
        $validator = Validator::make([
            'project_id' => 1,
            'jmd_design_criteria_id' => 1,
            'specified_strength_mpa' => 20,
            'statistical_factor_k' => 1.64,
            'standard_deviation_mpa' => 4,
            'strength_water_cement_ratio' => 0.55,
            'durability_maximum_water_cement_ratio' => 0.6,
            'manual_water_cement_ratio' => 0.5,
            'water_content_kg' => 200,
            'minimum_cement_kg' => 300,
            'cement_specific_gravity' => 3.15,
            'fine_aggregate_specific_gravity' => 2.6,
            'coarse_aggregate_specific_gravity' => 2.65,
            'coarse_aggregate_bulk_density_kg_m3' => 1600,
            'coarse_aggregate_volume_factor' => 0.62,
            'air_content_percent' => 2,
            'uses_admixture' => false,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('manual_override_reason', $validator->errors()->toArray());
        $this->assertArrayHasKey('standard_sources', $validator->errors()->toArray());
    }

    public function test_trial_and_strength_requests_enforce_geometry_and_load_units(): void
    {
        $trial = Validator::make([
            'project_id' => 1, 'mix_design_calculation_id' => 1, 'batch_number' => 'B-1',
            'specimen_type' => 'custom', 'specimen_count' => 1, 'waste_factor' => 1.2,
            'slump_volume_m3' => 0, 'manual_extra_volume_m3' => 0,
            'materials_per_m3' => ['cement' => 300, 'water' => 180, 'fine' => 700, 'coarse' => 1000],
        ], (new StoreTrialMixRequest)->rules());
        $this->assertTrue($trial->fails());
        $this->assertArrayHasKey('manual_specimen_volume_m3', $trial->errors()->toArray());

        $strength = Validator::make([
            'project_id' => 1, 'test_number' => 'CS-1', 'target_strength_mpa' => 20,
            'target_age_days' => 28, 'kg_cm2_per_mpa' => 10.197, 'minimum_statistical_samples' => 30,
            'specimens' => [[
                'number' => 'S-1', 'type' => 'cylinder', 'cast_at' => '2026-07-01',
                'tested_at' => '2026-07-29', 'maximum_load' => 450, 'load_unit' => 'psi',
                'diameter_mm' => 150, 'age_factor' => 1,
            ]],
        ], (new StoreCompressiveStrengthTestRequest)->rules());
        $this->assertTrue($strength->fails());
        $this->assertArrayHasKey('specimens.0.load_unit', $strength->errors()->toArray());
    }

    public function test_related_records_must_belong_to_the_selected_project(): void
    {
        $first = Project::create(['number' => 'OWN-1', 'name' => 'Proyek Pertama']);
        $second = Project::create(['number' => 'OWN-2', 'name' => 'Proyek Kedua']);
        $criteria = DesignCriterion::create(['project_id' => $second->id, 'revision_number' => 0]);
        $request = CalculateMixDesignRequest::create('/', 'POST', [
            'project_id' => $first->id,
            'jmd_design_criteria_id' => $criteria->id,
        ]);
        $validator = Validator::make([], []);
        foreach ($request->after() as $callback) {
            $callback($validator);
        }

        $this->assertArrayHasKey('jmd_design_criteria_id', $validator->errors()->toArray());
    }
}
