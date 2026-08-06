<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;
use InvalidArgumentException;

final class FieldBatchService
{
    public function scaleByVolume(array $materialsPerM3, float $targetVolumeM3): CalculationResult
    {
        if ($targetVolumeM3 <= 0) {
            throw new InvalidArgumentException('Volume target harus lebih besar dari nol.');
        }
        $materials = array_map(fn (mixed $mass): float => (float) $mass * $targetVolumeM3, $materialsPerM3);

        return CalculationResult::fromRaw(
            raw: ['target_volume_m3' => $targetVolumeM3, 'materials_kg' => $materials],
            units: ['target_volume_m3' => 'm3', 'materials_kg' => 'kg'],
            formulae: ['materials_kg' => 'komposisi kg/m3 x volume target'],
        );
    }

    public function perCementBag(array $weightRatios, float $bagWeightKg, array $bulkDensitiesKgM3 = []): CalculationResult
    {
        if ($bagWeightKg <= 0) {
            throw new InvalidArgumentException('Berat satu zak harus lebih besar dari nol.');
        }
        $weights = [];
        $volumes = [];
        foreach ($weightRatios as $material => $ratio) {
            $weights[$material] = (float) $ratio * $bagWeightKg;
            if (isset($bulkDensitiesKgM3[$material])) {
                if ((float) $bulkDensitiesKgM3[$material] <= 0) {
                    throw new InvalidArgumentException('Berat volume bahan harus lebih besar dari nol.');
                }
                $volumes[$material] = $weights[$material] / (float) $bulkDensitiesKgM3[$material];
            }
        }

        return CalculationResult::fromRaw(
            raw: ['bag_weight_kg' => $bagWeightKg, 'material_weights_kg' => $weights, 'material_volumes_m3' => $volumes],
            units: ['weight' => 'kg', 'volume' => 'm3'],
            formulae: ['material_weights_kg' => 'rasio berat x berat satu zak', 'material_volumes_m3' => 'berat bahan / berat volume bahan'],
            messages: ['Takaran volume lebih rendah ketelitiannya dibanding penimbangan berat.'],
        );
    }
}
