<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;
use App\Data\Jmd\CompressiveStrengthSpecimenData;
use App\Enums\SpecimenType;
use InvalidArgumentException;

final readonly class CompressiveStrengthService
{
    public function __construct(private Statistics $statistics) {}

    /** @param  array<CompressiveStrengthSpecimenData>  $specimens */
    public function calculate(array $specimens, float $targetMpa, float $kgCm2PerMpa, int $minimumStatisticalSamples = 30, string $source = ''): CalculationResult
    {
        if ($specimens === [] || $targetMpa <= 0 || $kgCm2PerMpa <= 0) {
            throw new InvalidArgumentException('Benda uji, mutu rencana, dan faktor konversi harus valid.');
        }

        $rows = [];
        $strengths = [];
        $estimated28 = [];
        foreach ($specimens as $specimen) {
            if ($specimen->maximumLoad <= 0 || $specimen->ageDays < 1 || $specimen->ageFactor <= 0) {
                throw new InvalidArgumentException('Beban, umur aktual, dan faktor umur harus lebih besar dari nol.');
            }
            $area = match ($specimen->type) {
                SpecimenType::Cylinder => $this->cylinderArea($specimen->diameterMm),
                SpecimenType::Cube => $this->rectangleArea($specimen->lengthMm, $specimen->widthMm ?? $specimen->lengthMm),
                default => $this->rectangleArea($specimen->lengthMm, $specimen->widthMm),
            };
            $newton = $this->toNewton($specimen->maximumLoad, $specimen->loadUnit);
            $strength = $newton / $area;
            $strengthKgCm2 = $strength * $kgCm2PerMpa;
            $estimate = $strength / $specimen->ageFactor;
            $rows[] = [
                'number' => $specimen->number, 'area_mm2' => $area, 'load_newton' => $newton,
                'strength_mpa' => $strength, 'strength_kg_cm2' => $strengthKgCm2,
                'estimated_28_day_mpa' => $estimate, 'meets_target' => $estimate >= $targetMpa,
            ];
            $strengths[] = $strength;
            $estimated28[] = $estimate;
        }

        $statistics = $this->statistics->summarize($strengths);
        $estimatedStatistics = $this->statistics->summarize($estimated28);
        $countMeeting = count(array_filter($rows, fn (array $row): bool => $row['meets_target']));
        $fullStatistics = count($rows) >= $minimumStatisticalSamples;
        $characteristicStrength = $fullStatistics && $statistics['sample_standard_deviation'] !== null
            ? $statistics['average'] - 1.64 * $statistics['sample_standard_deviation']
            : null;
        $messages = $fullStatistics ? [] : ['Data sampel belum mencukupi untuk evaluasi statistik penuh.'];

        return CalculationResult::fromRaw(
            raw: [
                'specimens' => $rows, 'statistics' => $statistics, 'estimated_28_day_statistics' => $estimatedStatistics,
                'meeting_count' => $countMeeting, 'meeting_percent' => $countMeeting / count($rows) * 100,
                'characteristic_strength_mpa' => $characteristicStrength, 'full_statistical_evaluation' => $fullStatistics,
                'status' => $estimatedStatistics['average'] >= $targetMpa ? 'meets' : 'does_not_meet',
            ],
            units: ['area' => 'mm2', 'load' => 'N', 'strength' => 'MPa', 'equivalent_strength' => 'kg/cm2'],
            formulae: [
                'cylinder_area' => 'pi/4 x d2', 'cube_area' => 'panjang x lebar', 'strength_mpa' => 'P / A',
                'estimated_28_day_mpa' => 'f aktual / faktor umur', 'characteristic_strength_mpa' => 'rata-rata - 1,64 x standar deviasi sampel',
            ],
            sources: ['age_factor' => $source, 'conversion' => $source],
            messages: $messages,
            valid: true,
        );
    }

    private function toNewton(float $load, string $unit): float
    {
        return match (strtolower($unit)) {
            'n' => $load,
            'kn' => $load * 1000,
            'kgf' => $load * 9.80665,
            'ton', 'tf', 'tonf' => $load * 9806.65,
            default => throw new InvalidArgumentException('Satuan beban tidak dikenali.'),
        };
    }

    private function cylinderArea(?float $diameterMm): float
    {
        if (($diameterMm ?? 0) <= 0) {
            throw new InvalidArgumentException('Diameter silinder harus lebih besar dari nol.');
        }

        return M_PI / 4 * $diameterMm ** 2;
    }

    private function rectangleArea(?float $lengthMm, ?float $widthMm): float
    {
        if (($lengthMm ?? 0) <= 0 || ($widthMm ?? 0) <= 0) {
            throw new InvalidArgumentException('Panjang dan lebar bidang tekan harus lebih besar dari nol.');
        }

        return $lengthMm * $widthMm;
    }
}
