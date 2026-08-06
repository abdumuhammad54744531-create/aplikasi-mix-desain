<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;
use InvalidArgumentException;

final readonly class CementSpecificGravityService
{
    public function __construct(private Statistics $statistics) {}

    public function calculate(array $observations, string $source): CalculationResult
    {
        if (count($observations) < 2) {
            throw new InvalidArgumentException('Minimal dua observasi berat jenis semen diperlukan.');
        }
        $rows = [];
        foreach ($observations as $observation) {
            $cementMass = (float) $observation['bottle_cement_kerosene_mass'] - (float) $observation['bottle_kerosene_mass'];
            $cementVolume = (float) $observation['final_reading_ml'] - (float) $observation['initial_reading_ml'];
            $waterDensity = (float) $observation['water_density'];
            if ($cementMass <= 0 || $cementVolume <= 0 || $waterDensity <= 0) {
                throw new InvalidArgumentException('Massa semen, volume semen, dan densitas air harus lebih besar dari nol.');
            }
            $density = $cementMass / $cementVolume;
            $rows[] = ['cement_mass' => $cementMass, 'cement_volume' => $cementVolume, 'cement_density' => $density, 'specific_gravity' => $density / $waterDensity];
        }
        $averages = [];
        foreach (['cement_mass', 'cement_volume', 'cement_density', 'specific_gravity'] as $key) {
            $averages[$key] = $this->statistics->summarize(array_column($rows, $key))['average'];
        }

        return CalculationResult::fromRaw(
            raw: ['observations' => $rows, 'averages' => $averages],
            units: ['cement_mass' => 'g', 'cement_volume' => 'ml', 'cement_density' => 'g/ml', 'specific_gravity' => null],
            formulae: ['cement_mass' => 'M = (botol + semen + kerosin) - (botol + kerosin)', 'cement_volume' => 'V = bacaan akhir - bacaan awal', 'cement_density' => 'rho = M / V', 'specific_gravity' => 'SG = rho semen / rho air'],
            sources: ['all' => $source],
        );
    }
}
