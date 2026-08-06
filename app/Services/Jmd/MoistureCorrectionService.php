<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;
use App\Data\Jmd\MoistureCorrectionData;
use InvalidArgumentException;

final class MoistureCorrectionService
{
    public function calculate(MoistureCorrectionData $input): CalculationResult
    {
        foreach ([$input->fineSsdMassKg, $input->coarseSsdMassKg, $input->designWaterKg, $input->cementMassKg] as $value) {
            if ($value < 0) {
                throw new InvalidArgumentException('Massa koreksi kelembapan tidak boleh negatif.');
            }
        }
        foreach ([$input->fineMoisturePercent, $input->fineAbsorptionPercent, $input->coarseMoisturePercent, $input->coarseAbsorptionPercent] as $value) {
            if ($value < 0) {
                throw new InvalidArgumentException('Kadar air dan penyerapan tidak boleh negatif.');
            }
        }
        if ($input->cementMassKg <= 0) {
            throw new InvalidArgumentException('Massa semen harus lebih besar dari nol.');
        }

        $fineDry = $input->fineSsdMassKg / (1 + $input->fineAbsorptionPercent / 100);
        $coarseDry = $input->coarseSsdMassKg / (1 + $input->coarseAbsorptionPercent / 100);
        $fineField = $fineDry * (1 + $input->fineMoisturePercent / 100);
        $coarseField = $coarseDry * (1 + $input->coarseMoisturePercent / 100);
        $fineFreeWater = $fineDry * ($input->fineMoisturePercent - $input->fineAbsorptionPercent) / 100;
        $coarseFreeWater = $coarseDry * ($input->coarseMoisturePercent - $input->coarseAbsorptionPercent) / 100;
        $totalFreeWater = $fineFreeWater + $coarseFreeWater;
        $mixerWater = $input->designWaterKg - $totalFreeWater;
        $messages = [];
        $valid = true;
        if ($mixerWater < 0) {
            $messages[] = 'Air bebas agregat melebihi air rencana; hentikan dan verifikasi kondisi material. Nilai tidak dijepit menjadi nol.';
            $valid = false;
        }
        $effectiveWaterCementRatio = ($mixerWater + $totalFreeWater) / $input->cementMassKg;

        return CalculationResult::fromRaw(
            raw: [
                'fine_dry_mass_kg' => $fineDry, 'coarse_dry_mass_kg' => $coarseDry,
                'fine_field_mass_kg' => $fineField, 'coarse_field_mass_kg' => $coarseField,
                'fine_free_water_kg' => $fineFreeWater, 'coarse_free_water_kg' => $coarseFreeWater,
                'total_free_water_kg' => $totalFreeWater, 'mixer_water_kg' => $mixerWater,
                'effective_water_cement_ratio' => $effectiveWaterCementRatio,
            ],
            units: ['mass' => 'kg', 'moisture' => '%', 'water_cement_ratio' => null],
            formulae: [
                'dry_mass' => 'Wkering = WSSD / (1 + Abs)', 'field_mass' => 'Wlapangan = Wkering x (1 + MC)',
                'free_water' => 'Wkering x (MC - Abs)', 'mixer_water' => 'air rencana - total air bebas agregat',
                'effective_water_cement_ratio' => '(air mixer + air bebas agregat) / semen',
            ],
            messages: $messages,
            log: ["Air bebas pasir = {$fineFreeWater} kg", "Air bebas kerikil = {$coarseFreeWater} kg", "Air mixer = {$input->designWaterKg} - {$totalFreeWater} = {$mixerWater} kg"],
            valid: $valid,
        );
    }
}
