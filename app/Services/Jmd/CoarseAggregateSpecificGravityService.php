<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;
use App\Data\Jmd\CoarseAggregateSpecificGravityData;
use InvalidArgumentException;

final readonly class CoarseAggregateSpecificGravityService
{
    public function __construct(private Statistics $statistics) {}

    /** @param  array<CoarseAggregateSpecificGravityData>  $observations */
    public function calculate(array $observations, string $source = 'SNI 1969:2016'): CalculationResult
    {
        if (count($observations) < 2) {
            throw new InvalidArgumentException('Minimal dua observasi berat jenis agregat kasar diperlukan.');
        }

        $rows = [];
        foreach ($observations as $observation) {
            if ($observation->ssdAirMass <= $observation->submergedMass || $observation->ovenDryMass <= $observation->submergedMass) {
                throw new InvalidArgumentException('Massa SSD dan kering oven harus lebih besar dari massa dalam air.');
            }
            if ($observation->ovenDryMass <= 0) {
                throw new InvalidArgumentException('Massa kering oven harus lebih besar dari nol.');
            }
            $rows[] = [
                'bulk_dry' => $observation->ovenDryMass / ($observation->ssdAirMass - $observation->submergedMass),
                'bulk_ssd' => $observation->ssdAirMass / ($observation->ssdAirMass - $observation->submergedMass),
                'apparent' => $observation->ovenDryMass / ($observation->ovenDryMass - $observation->submergedMass),
                'absorption' => ($observation->ssdAirMass - $observation->ovenDryMass) / $observation->ovenDryMass * 100,
            ];
        }
        $averages = [];
        foreach (['bulk_dry', 'bulk_ssd', 'apparent', 'absorption'] as $key) {
            $averages[$key] = $this->statistics->summarize(array_column($rows, $key))['average'];
        }
        $messages = $averages['absorption'] < 0 ? ['Penyerapan negatif harus diverifikasi.'] : [];

        return CalculationResult::fromRaw(
            raw: ['observations' => $rows, 'averages' => $averages],
            units: ['bulk_dry' => null, 'bulk_ssd' => null, 'apparent' => null, 'absorption' => '%'],
            formulae: [
                'bulk_dry' => 'C / (A - B)', 'bulk_ssd' => 'A / (A - B)',
                'apparent' => 'C / (C - B)', 'absorption' => '((A - C) / C) x 100%',
            ],
            sources: ['all' => $source],
            messages: $messages,
            valid: $messages === [],
        );
    }
}
