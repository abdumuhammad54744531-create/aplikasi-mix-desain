<?php

namespace Tests\Unit;

use App\Data\Jmd\AbrasionObservationData;
use App\Data\Jmd\BulkDensityObservationData;
use App\Data\Jmd\CoarseAggregateSpecificGravityData;
use App\Data\Jmd\CompressiveStrengthSpecimenData;
use App\Data\Jmd\FineAggregateSpecificGravityData;
use App\Data\Jmd\MixDesignInputData;
use App\Data\Jmd\MoistureCorrectionData;
use App\Data\Jmd\MoistureObservationData;
use App\Data\Jmd\SiltObservationData;
use App\Data\Jmd\TrialMixData;
use App\Enums\AggregateType;
use App\Enums\SpecimenType;
use App\Services\Jmd\AbrasionService;
use App\Services\Jmd\BulkDensityService;
use App\Services\Jmd\CoarseAggregateSpecificGravityService;
use App\Services\Jmd\CompressiveStrengthService;
use App\Services\Jmd\FineAggregateSpecificGravityService;
use App\Services\Jmd\JmdReportService;
use App\Services\Jmd\JmdValidationService;
use App\Services\Jmd\MixDesignService;
use App\Services\Jmd\MoistureContentService;
use App\Services\Jmd\MoistureCorrectionService;
use App\Services\Jmd\SieveAnalysisService;
use App\Services\Jmd\SiltContentService;
use App\Services\Jmd\Statistics;
use App\Services\Jmd\TrialMixService;
use PHPUnit\Framework\TestCase;

class JmdFormulaServicesTest extends TestCase
{
    private Statistics $statistics;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statistics = new Statistics;
    }

    public function test_material_observations_keep_raw_precision_and_produce_statistics(): void
    {
        $moisture = (new MoistureContentService($this->statistics))->calculate([
            new MoistureObservationData(20, 120, 115),
            new MoistureObservationData(21, 121, 116),
        ]);
        $this->assertEqualsWithDelta(5.2631578947, $moisture->raw['statistics']['average'], 1e-9);
        $this->assertSame(5.263158, $moisture->rounded['statistics']['average']);
        $this->assertNotSame($moisture->raw['statistics']['average'], $moisture->rounded['statistics']['average']);

        $silt = (new SiltContentService($this->statistics))->calculate([
            new SiltObservationData(50, 1050, 1029.2),
            new SiltObservationData(50, 1050, 1029.2),
        ], 5, 'Master kadar lumpur');
        $this->assertEqualsWithDelta(2.08, $silt->raw['statistics']['average'], 1e-10);
        $this->assertSame('meets', $silt->raw['status']);
    }

    public function test_specific_gravity_bulk_density_and_abrasion_services(): void
    {
        $fine = (new FineAggregateSpecificGravityService($this->statistics))->calculate([
            new FineAggregateSpecificGravityData(150, 500, 980, 680, 490),
            new FineAggregateSpecificGravityData(150, 500, 980, 680, 490),
        ]);
        $this->assertEqualsWithDelta(2.45, $fine->raw['averages']['bulk_dry'], 1e-10);
        $this->assertEqualsWithDelta(2.5, $fine->raw['averages']['bulk_ssd'], 1e-10);

        $coarse = (new CoarseAggregateSpecificGravityService($this->statistics))->calculate([
            new CoarseAggregateSpecificGravityData(1000, 600, 980),
            new CoarseAggregateSpecificGravityData(1000, 600, 980),
        ]);
        $this->assertEqualsWithDelta(2.45, $coarse->raw['averages']['bulk_dry'], 1e-10);

        $density = (new BulkDensityService($this->statistics))->calculate([
            new BulkDensityObservationData('loose', 10000, 5, 20),
            new BulkDensityObservationData('loose', 10000, 5, 20),
        ]);
        $this->assertEqualsWithDelta(1500, $density->raw['averages_kg_m3']['loose'], 1e-10);

        $abrasion = (new AbrasionService($this->statistics))->calculate([
            new AbrasionObservationData(5000, 3267.5),
            new AbrasionObservationData(5000, 3267.5),
        ], 40, 'Master abrasi');
        $this->assertEqualsWithDelta(34.65, $abrasion->raw['statistics']['average'], 1e-10);
        $this->assertSame('meets', $abrasion->raw['status']);
    }

    public function test_sieve_analysis_uses_initial_mass_and_reports_mass_balance_and_bounds(): void
    {
        $result = (new SieveAnalysisService)->calculate(AggregateType::Fine, 1000, [
            ['sieve_size_mm' => 4.75, 'retained_mass' => 50, 'lower_limit_percent' => 90, 'upper_limit_percent' => 100],
            ['sieve_size_mm' => 2.36, 'retained_mass' => 150, 'lower_limit_percent' => 70, 'upper_limit_percent' => 100],
            ['sieve_size_mm' => 1.18, 'retained_mass' => 200, 'lower_limit_percent' => 50, 'upper_limit_percent' => 90],
            ['sieve_size_mm' => 0.60, 'retained_mass' => 200, 'lower_limit_percent' => 30, 'upper_limit_percent' => 70],
            ['sieve_size_mm' => 0.30, 'retained_mass' => 200, 'lower_limit_percent' => 10, 'upper_limit_percent' => 40],
            ['sieve_size_mm' => 0.15, 'retained_mass' => 150, 'lower_limit_percent' => 0, 'upper_limit_percent' => 15],
            ['sieve_size_mm' => null, 'is_pan' => true, 'retained_mass' => 50],
        ], 0.5, 'Master gradasi');
        $this->assertSame(1000.0, $result->raw['total_retained_mass']);
        $this->assertSame(0.0, $result->raw['loss_percent']);
        $this->assertSame('meets', $result->raw['mass_balance_status']);
        $this->assertEqualsWithDelta(3.0, $result->raw['fineness_modulus'], 1e-10);
    }

    public function test_mix_design_moisture_trial_and_strength_flow_is_transparent(): void
    {
        $mix = (new MixDesignService)->calculate(new MixDesignInputData(
            specifiedStrengthMpa: 20, statisticalFactorK: 1.64, standardDeviationMpa: 4,
            strengthWaterCementRatio: 0.55, durabilityMaximumWaterCementRatio: 0.60,
            waterContentKg: 204.72, minimumCementKg: 300, maximumCementKg: 500,
            cementSpecificGravity: 3.15, fineAggregateSpecificGravity: 2.6,
            coarseAggregateSpecificGravity: 2.65, coarseAggregateBulkDensityKgM3: 1600,
            coarseAggregateVolumeFactor: 0.62,
        ), ['water' => 'Master kadar air bebas', 'wc_ratio' => 'Master FAS']);
        $this->assertSame(0.55, $mix->raw['used_water_cement_ratio']);
        $this->assertEqualsWithDelta(372.218181818, $mix->raw['cement_kg'], 1e-9);
        $this->assertEqualsWithDelta(1, $mix->raw['total_absolute_volume_m3'], 1e-12);

        $correction = (new MoistureCorrectionService)->calculate(new MoistureCorrectionData(
            $mix->raw['fine_aggregate_ssd_kg'], 5, 2, $mix->raw['coarse_aggregate_ssd_kg'], 1, 2,
            $mix->raw['water_kg'], $mix->raw['cement_kg'],
        ));
        $this->assertGreaterThan(0, $correction->raw['fine_free_water_kg']);
        $this->assertLessThan(0, $correction->raw['coarse_free_water_kg']);

        $trial = (new TrialMixService)->calculate(new TrialMixData(
            SpecimenType::Cylinder, 3, 1.2, 0.005, 0, diameterMm: 150, heightMm: 300,
        ), ['cement' => $mix->raw['cement_kg'], 'water' => $mix->raw['water_kg'], 'fine' => $mix->raw['fine_aggregate_ssd_kg'], 'coarse' => $mix->raw['coarse_aggregate_ssd_kg']]);
        $this->assertGreaterThan(0.02, $trial->raw['total_trial_volume_m3']);

        $strength = (new CompressiveStrengthService($this->statistics))->calculate([
            new CompressiveStrengthSpecimenData('S-1', SpecimenType::Cylinder, 450, 'kN', 28, 1, diameterMm: 150),
            new CompressiveStrengthSpecimenData('S-2', SpecimenType::Cylinder, 460, 'kN', 28, 1, diameterMm: 150),
            new CompressiveStrengthSpecimenData('S-3', SpecimenType::Cylinder, 440, 'kN', 28, 1, diameterMm: 150),
        ], 20, 10.19716213, 30, 'Master faktor umur');
        $this->assertCount(3, $strength->raw['specimens']);
        $this->assertFalse($strength->raw['full_statistical_evaluation']);
        $this->assertNull($strength->raw['characteristic_strength_mpa']);
        $this->assertContains('Data sampel belum mencukupi untuk evaluasi statistik penuh.', $strength->messages);
    }

    public function test_validation_and_report_hash_are_order_independent_but_content_sensitive(): void
    {
        $validation = (new JmdValidationService)->evaluate([
            'material' => ['available' => true, 'meets' => true],
            'strength' => ['available' => false],
        ]);
        $this->assertSame('needs_verification', $validation->raw['overall_status']);

        $report = new JmdReportService;
        $first = $report->snapshot(['name' => 'A', 'number' => '1'], ['b' => 2, 'a' => 1], [], 0);
        $second = $report->snapshot(['number' => '1', 'name' => 'A'], ['a' => 1, 'b' => 2], [], 0);
        $changed = $report->snapshot(['number' => '1', 'name' => 'B'], ['a' => 1, 'b' => 2], [], 0);
        $this->assertSame($first['hash'], $second['hash']);
        $this->assertNotSame($first['hash'], $changed['hash']);
    }
}
