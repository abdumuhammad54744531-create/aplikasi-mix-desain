<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;
use InvalidArgumentException;

final class ConcreteStrengthService
{
    public function targetMean(float $specifiedStrengthMpa, float $k, float $standardDeviationMpa, string $source): CalculationResult
    {
        if ($specifiedStrengthMpa <= 0 || $k <= 0 || $standardDeviationMpa < 0) {
            throw new InvalidArgumentException('Mutu, faktor statistik, dan standar deviasi tidak valid.');
        }
        $margin = $k * $standardDeviationMpa;
        $target = $specifiedStrengthMpa + $margin;

        return CalculationResult::fromRaw(
            raw: ['specified_strength_mpa' => $specifiedStrengthMpa, 'margin_mpa' => $margin, 'target_mean_strength_mpa' => $target],
            units: ['specified_strength_mpa' => 'MPa', 'margin_mpa' => 'MPa', 'target_mean_strength_mpa' => 'MPa'],
            formulae: ['margin_mpa' => 'M = k x S', 'target_mean_strength_mpa' => "f'cr = f'c + M"],
            sources: ['statistical_factor' => $source, 'standard_deviation' => $source],
            log: ["M = {$k} x {$standardDeviationMpa} = {$margin} MPa", "f'cr = {$specifiedStrengthMpa} + {$margin} = {$target} MPa"],
        );
    }

    public function convertKToMpa(float $kStrengthKgCm2, float $kgCm2PerMpa, float $cubeToCylinderFactor, string $source): CalculationResult
    {
        if ($kStrengthKgCm2 <= 0 || $kgCm2PerMpa <= 0 || $cubeToCylinderFactor <= 0) {
            throw new InvalidArgumentException('Nilai dan faktor konversi mutu harus lebih besar dari nol.');
        }
        $cubeMpa = $kStrengthKgCm2 / $kgCm2PerMpa;
        $cylinderMpa = $cubeMpa * $cubeToCylinderFactor;

        return CalculationResult::fromRaw(
            raw: ['k_strength_kg_cm2' => $kStrengthKgCm2, 'cube_strength_mpa' => $cubeMpa, 'cylinder_strength_mpa' => $cylinderMpa, 'cube_to_cylinder_factor' => $cubeToCylinderFactor],
            units: ['k_strength_kg_cm2' => 'kg/cm2', 'cube_strength_mpa' => 'MPa', 'cylinder_strength_mpa' => 'MPa'],
            formulae: ['cube_strength_mpa' => 'K / faktor kg/cm2 per MPa', 'cylinder_strength_mpa' => 'mutu kubus MPa x faktor kubus-ke-silinder'],
            sources: ['conversion' => $source],
        );
    }
}
