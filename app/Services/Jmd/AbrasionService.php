<?php

namespace App\Services\Jmd;

use App\Data\Jmd\AbrasionObservationData;
use App\Data\Jmd\CalculationResult;
use InvalidArgumentException;

final readonly class AbrasionService
{
    public function __construct(private Statistics $statistics) {}

    /** @param  array<AbrasionObservationData>  $observations */
    public function calculate(array $observations, float $limitPercent, string $source): CalculationResult
    {
        if (count($observations) < 2) {
            throw new InvalidArgumentException('Minimal dua observasi abrasi diperlukan.');
        }
        $values = [];
        $rows = [];
        foreach ($observations as $observation) {
            if ($observation->initialMass <= 0 || $observation->retainedNo12Mass < 0 || $observation->retainedNo12Mass > $observation->initialMass) {
                throw new InvalidArgumentException('Massa awal/tertahan abrasi tidak valid.');
            }
            $abrasion = ($observation->initialMass - $observation->retainedNo12Mass) / $observation->initialMass * 100;
            $rows[] = ['abrasion' => $abrasion];
            $values[] = $abrasion;
        }
        $statistics = $this->statistics->summarize($values);
        $status = $statistics['average'] <= $limitPercent ? 'meets' : 'does_not_meet';

        return CalculationResult::fromRaw(
            raw: ['observations' => $rows, 'statistics' => $statistics, 'limit_percent' => $limitPercent, 'status' => $status],
            units: ['abrasion' => '%', 'limit_percent' => '%'],
            formulae: ['abrasion' => '((massa awal - massa tertahan No. 12) / massa awal) x 100%'],
            sources: ['limit_percent' => $source],
        );
    }
}
