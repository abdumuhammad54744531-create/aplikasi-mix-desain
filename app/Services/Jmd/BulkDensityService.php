<?php

namespace App\Services\Jmd;

use App\Data\Jmd\BulkDensityObservationData;
use App\Data\Jmd\CalculationResult;
use InvalidArgumentException;

final readonly class BulkDensityService
{
    public function __construct(private Statistics $statistics) {}

    /** @param  array<BulkDensityObservationData>  $observations */
    public function calculate(array $observations, string $massUnit = 'kg', string $source = ''): CalculationResult
    {
        if (count($observations) < 2) {
            throw new InvalidArgumentException('Minimal dua observasi berat volume diperlukan.');
        }
        if (! in_array($massUnit, ['kg', 'g'], true)) {
            throw new InvalidArgumentException('Satuan massa harus kg atau g.');
        }

        $rows = [];
        $grouped = [];
        foreach ($observations as $observation) {
            $mass = $observation->filledMouldMass - $observation->mouldMass;
            if ($mass <= 0 || $observation->mouldVolumeCm3 <= 0) {
                throw new InvalidArgumentException('Massa bahan dan volume mould harus lebih besar dari nol.');
            }
            $densityKgM3 = $massUnit === 'kg'
                ? $mass / $observation->mouldVolumeCm3 * 1_000_000
                : $mass / $observation->mouldVolumeCm3 * 1_000;
            $rows[] = ['condition' => $observation->condition, 'sample_mass' => $mass, 'density_kg_m3' => $densityKgM3, 'density_g_cm3' => $densityKgM3 / 1000, 'density_ton_m3' => $densityKgM3 / 1000];
            $grouped[$observation->condition][] = $densityKgM3;
        }

        $averages = [];
        foreach ($grouped as $condition => $values) {
            $averages[$condition] = $this->statistics->summarize($values)['average'];
        }

        return CalculationResult::fromRaw(
            raw: ['observations' => $rows, 'averages_kg_m3' => $averages],
            units: ['sample_mass' => $massUnit, 'density_kg_m3' => 'kg/m3', 'density_g_cm3' => 'g/cm3', 'density_ton_m3' => 'ton/m3'],
            formulae: ['sample_mass' => 'B = (mould + bahan) - mould', 'density_kg_m3' => 'rho = B / V, kemudian dikonversi ke kg/m3'],
            sources: ['all' => $source],
        );
    }
}
