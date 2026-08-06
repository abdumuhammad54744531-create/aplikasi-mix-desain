<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;
use App\Data\Jmd\TrialMixData;
use App\Enums\SpecimenType;
use InvalidArgumentException;

final class TrialMixService
{
    public function calculate(TrialMixData $input, array $materialsPerM3, int $weighingPrecision = 2): CalculationResult
    {
        if ($input->specimenCount < 1 || $input->wasteFactor <= 0 || $input->slumpVolumeM3 < 0 || $input->manualExtraVolumeM3 < 0) {
            throw new InvalidArgumentException('Jumlah benda uji, waste factor, dan volume tambahan tidak valid.');
        }
        $specimenVolume = match ($input->specimenType) {
            SpecimenType::Cylinder => $this->cylinderVolume($input->diameterMm, $input->heightMm),
            SpecimenType::Cube => $this->boxVolume($input->lengthMm, $input->widthMm ?? $input->lengthMm, $input->heightMm ?? $input->lengthMm),
            SpecimenType::Beam => $this->boxVolume($input->lengthMm, $input->widthMm, $input->heightMm),
            SpecimenType::Custom => $input->manualSpecimenVolumeM3 ?? throw new InvalidArgumentException('Volume bentuk khusus wajib diisi.'),
        };
        if ($specimenVolume <= 0) {
            throw new InvalidArgumentException('Volume satu benda uji harus lebih besar dari nol.');
        }
        $totalVolume = $input->specimenCount * $specimenVolume * $input->wasteFactor + $input->slumpVolumeM3 + $input->manualExtraVolumeM3;
        $materials = [];
        foreach ($materialsPerM3 as $code => $massPerM3) {
            if ((float) $massPerM3 < 0) {
                throw new InvalidArgumentException('Komposisi material per m3 tidak boleh negatif.');
            }
            $theoretical = (float) $massPerM3 * $totalVolume;
            $weighing = round($theoretical, $weighingPrecision);
            $materials[$code] = ['theoretical_mass_kg' => $theoretical, 'weighing_mass_kg' => $weighing, 'rounding_difference_kg' => $weighing - $theoretical];
        }

        return CalculationResult::fromRaw(
            raw: ['specimen_volume_m3' => $specimenVolume, 'total_trial_volume_m3' => $totalVolume, 'materials' => $materials],
            units: ['volume' => 'm3', 'material_mass' => 'kg'],
            formulae: ['cylinder_volume' => 'pi/4 x d2 x h', 'cube_volume' => 's3', 'total_trial_volume' => 'jumlah x volume benda uji x waste factor + volume slump + volume manual', 'material_mass' => 'komposisi kg/m3 x volume trial'],
            log: ["Volume trial = {$input->specimenCount} x {$specimenVolume} x {$input->wasteFactor} + {$input->slumpVolumeM3} + {$input->manualExtraVolumeM3} = {$totalVolume} m3"],
        );
    }

    private function cylinderVolume(?float $diameterMm, ?float $heightMm): float
    {
        if (($diameterMm ?? 0) <= 0 || ($heightMm ?? 0) <= 0) {
            throw new InvalidArgumentException('Diameter dan tinggi silinder harus lebih besar dari nol.');
        }

        return M_PI / 4 * ($diameterMm / 1000) ** 2 * ($heightMm / 1000);
    }

    private function boxVolume(?float $lengthMm, ?float $widthMm, ?float $heightMm): float
    {
        if (($lengthMm ?? 0) <= 0 || ($widthMm ?? 0) <= 0 || ($heightMm ?? 0) <= 0) {
            throw new InvalidArgumentException('Dimensi benda uji harus lebih besar dari nol.');
        }

        return $lengthMm / 1000 * ($widthMm / 1000) * ($heightMm / 1000);
    }
}
