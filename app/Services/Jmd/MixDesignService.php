<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;
use App\Data\Jmd\MixDesignInputData;
use InvalidArgumentException;

final class MixDesignService
{
    public function calculate(MixDesignInputData $input, array $sources): CalculationResult
    {
        foreach ([
            $input->specifiedStrengthMpa, $input->statisticalFactorK, $input->strengthWaterCementRatio,
            $input->durabilityMaximumWaterCementRatio, $input->waterContentKg, $input->cementSpecificGravity,
            $input->fineAggregateSpecificGravity, $input->coarseAggregateSpecificGravity,
            $input->coarseAggregateBulkDensityKgM3, $input->coarseAggregateVolumeFactor,
        ] as $value) {
            if ($value <= 0) {
                throw new InvalidArgumentException('Input utama Mix Design harus lebih besar dari nol.');
            }
        }
        if ($input->manualWaterCementRatio !== null && blank($input->manualOverrideReason)) {
            throw new InvalidArgumentException('Override FAS wajib disertai alasan.');
        }

        $margin = $input->statisticalFactorK * $input->standardDeviationMpa;
        $target = $input->specifiedStrengthMpa + $margin;
        $strictWaterCementRatio = min($input->strengthWaterCementRatio, $input->durabilityMaximumWaterCementRatio);
        $waterCementRatio = $input->manualWaterCementRatio ?? $strictWaterCementRatio;
        $calculatedCement = $input->waterContentKg / $waterCementRatio;
        $cement = max($calculatedCement, $input->minimumCementKg);
        $messages = [];
        $valid = true;
        if ($input->maximumCementKg !== null && $cement > $input->maximumCementKg) {
            $messages[] = 'Kadar semen hasil perhitungan melampaui batas maksimum.';
            $valid = false;
        }

        $coarseMass = $input->coarseAggregateBulkDensityKgM3 * $input->coarseAggregateVolumeFactor;
        $cementVolume = $cement / ($input->cementSpecificGravity * 1000);
        $waterVolume = $input->waterContentKg / 1000;
        $coarseVolume = $coarseMass / ($input->coarseAggregateSpecificGravity * 1000);
        $airVolume = $input->airContentPercent / 100;
        $admixtureVolume = $input->admixtureMassKg > 0
            ? $input->admixtureMassKg / (($input->admixtureSpecificGravity ?? throw new InvalidArgumentException('Berat jenis bahan tambah wajib diisi.')) * 1000)
            : 0.0;
        $fineVolume = 1 - $cementVolume - $waterVolume - $coarseVolume - $airVolume - $admixtureVolume;
        if ($fineVolume <= 0) {
            throw new InvalidArgumentException('Jumlah volume selain agregat halus sudah mencapai atau melebihi 1 m3.');
        }
        $fineMass = $fineVolume * $input->fineAggregateSpecificGravity * 1000;
        $totalAbsoluteVolume = $cementVolume + $waterVolume + $coarseVolume + $fineVolume + $airVolume + $admixtureVolume;
        $totalMass = $cement + $input->waterContentKg + $fineMass + $coarseMass + $input->admixtureMassKg;

        $raw = [
            'specified_strength_mpa' => $input->specifiedStrengthMpa, 'margin_mpa' => $margin,
            'target_mean_strength_mpa' => $target, 'strength_water_cement_ratio' => $input->strengthWaterCementRatio,
            'durability_maximum_water_cement_ratio' => $input->durabilityMaximumWaterCementRatio,
            'strict_water_cement_ratio' => $strictWaterCementRatio, 'used_water_cement_ratio' => $waterCementRatio,
            'calculated_cement_kg' => $calculatedCement, 'cement_kg' => $cement,
            'water_kg' => $input->waterContentKg, 'fine_aggregate_ssd_kg' => $fineMass,
            'coarse_aggregate_ssd_kg' => $coarseMass, 'admixture_kg' => $input->admixtureMassKg,
            'volumes_m3' => ['cement' => $cementVolume, 'water' => $waterVolume, 'fine_aggregate' => $fineVolume, 'coarse_aggregate' => $coarseVolume, 'air' => $airVolume, 'admixture' => $admixtureVolume],
            'total_absolute_volume_m3' => $totalAbsoluteVolume, 'volume_difference_m3' => 1 - $totalAbsoluteVolume,
            'total_mass_kg' => $totalMass,
            'weight_ratios' => ['cement' => 1.0, 'fine_aggregate' => $fineMass / $cement, 'coarse_aggregate' => $coarseMass / $cement, 'water' => $input->waterContentKg / $cement],
        ];

        return CalculationResult::fromRaw(
            raw: $raw,
            units: ['strength' => 'MPa', 'mass' => 'kg/m3', 'volume' => 'm3', 'water_cement_ratio' => null],
            formulae: [
                'margin' => 'M = k x S', 'target' => "f'cr = f'c + M", 'used_water_cement_ratio' => 'minimum(FAS kuat tekan, FAS durabilitas)',
                'cement' => 'C = W / FAS, kemudian diperiksa terhadap semen minimum/maksimum',
                'absolute_volume' => 'V = massa / (SG x 1000)', 'fine_volume' => '1 - jumlah volume komponen lain',
            ],
            sources: $sources,
            messages: $messages,
            log: ["FAS ketat = min({$input->strengthWaterCementRatio}, {$input->durabilityMaximumWaterCementRatio}) = {$strictWaterCementRatio}", "Semen = {$input->waterContentKg} / {$waterCementRatio} = {$calculatedCement} kg/m3", "Jumlah volume absolut = {$totalAbsoluteVolume} m3"],
            valid: $valid,
        );
    }
}
