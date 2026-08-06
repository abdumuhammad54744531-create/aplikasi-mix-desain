<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;
use App\Data\Jmd\FineAggregateSpecificGravityData;
use InvalidArgumentException;

final readonly class FineAggregateSpecificGravityService
{
    public function __construct(private Statistics $statistics) {}

    /** @param  array<FineAggregateSpecificGravityData>  $observations */
    public function calculate(array $observations, string $source = 'SNI 1970:2016'): CalculationResult
    {
        if (count($observations) < 2) {
            throw new InvalidArgumentException('Minimal dua observasi berat jenis agregat halus diperlukan.');
        }

        $rows = [];
        foreach ($observations as $observation) {
            $bulkDenominator = $observation->pycnometerWaterMass + $observation->ssdSampleMass - $observation->pycnometerSampleWaterMass;
            $apparentDenominator = $observation->pycnometerWaterMass + $observation->ovenDrySampleMass - $observation->pycnometerSampleWaterMass;
            if ($bulkDenominator <= 0 || $apparentDenominator <= 0 || $observation->ovenDrySampleMass <= 0) {
                throw new InvalidArgumentException('Penyebut berat jenis agregat halus harus lebih besar dari nol.');
            }
            $rows[] = [
                'bulk_dry' => $observation->ovenDrySampleMass / $bulkDenominator,
                'bulk_ssd' => $observation->ssdSampleMass / $bulkDenominator,
                'apparent' => $observation->ovenDrySampleMass / $apparentDenominator,
                'absorption' => ($observation->ssdSampleMass - $observation->ovenDrySampleMass) / $observation->ovenDrySampleMass * 100,
            ];
        }

        $averages = [];
        foreach (['bulk_dry', 'bulk_ssd', 'apparent', 'absorption'] as $key) {
            $averages[$key] = $this->statistics->summarize(array_column($rows, $key))['average'];
        }

        return CalculationResult::fromRaw(
            raw: ['observations' => $rows, 'averages' => $averages],
            units: ['bulk_dry' => null, 'bulk_ssd' => null, 'apparent' => null, 'absorption' => '%'],
            formulae: [
                'bulk_dry' => 'E / (D + B - C)', 'bulk_ssd' => 'B / (D + B - C)',
                'apparent' => 'E / (D + E - C)', 'absorption' => '((B - E) / E) x 100%',
            ],
            sources: ['all' => $source],
        );
    }
}
