<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;
use App\Data\Jmd\MoistureObservationData;
use InvalidArgumentException;

final readonly class MoistureContentService
{
    public function __construct(private Statistics $statistics) {}

    /** @param  array<MoistureObservationData>  $observations */
    public function calculate(array $observations, string $source = 'SNI 1971:2011'): CalculationResult
    {
        if (count($observations) < 2) {
            throw new InvalidArgumentException('Minimal dua observasi kadar air diperlukan.');
        }

        $rows = [];
        $values = [];
        $messages = [];
        $valid = true;

        foreach ($observations as $index => $observation) {
            if ($observation->wetContainerMass <= $observation->containerMass) {
                throw new InvalidArgumentException('W2 harus lebih besar dari W1.');
            }
            if ($observation->dryContainerMass <= $observation->containerMass) {
                throw new InvalidArgumentException('W3 harus lebih besar dari W1 dan penyebut tidak boleh nol.');
            }

            $wetSampleMass = $observation->wetContainerMass - $observation->containerMass;
            $drySampleMass = $observation->dryContainerMass - $observation->containerMass;
            $moisture = ($wetSampleMass - $drySampleMass) / $drySampleMass * 100;
            if ($observation->wetContainerMass < $observation->dryContainerMass) {
                $valid = false;
                $messages[] = 'Observasi '.($index + 1).': W2 lebih kecil dari W3; kadar air negatif perlu verifikasi.';
            }
            $rows[] = compact('wetSampleMass', 'drySampleMass', 'moisture');
            $values[] = $moisture;
        }

        return CalculationResult::fromRaw(
            raw: ['observations' => $rows, 'statistics' => $this->statistics->summarize($values)],
            units: ['wetSampleMass' => 'g', 'drySampleMass' => 'g', 'moisture' => '%'],
            formulae: ['wetSampleMass' => 'W4 = W2 - W1', 'drySampleMass' => 'W5 = W3 - W1', 'moisture' => 'KA = ((W4 - W5) / W5) x 100%'],
            sources: ['moisture' => $source],
            messages: $messages,
            log: array_map(fn (array $row, int $index): string => sprintf('Observasi %d: KA = ((%s - %s) / %s) x 100%% = %s%%', $index + 1, $row['wetSampleMass'], $row['drySampleMass'], $row['drySampleMass'], $row['moisture']), $rows, array_keys($rows)),
            valid: $valid,
        );
    }
}
