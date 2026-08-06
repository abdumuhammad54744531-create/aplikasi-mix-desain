<?php

namespace App\Services\Jmd;

use InvalidArgumentException;

final class Statistics
{
    public function summarize(array $values): array
    {
        if ($values === []) {
            throw new InvalidArgumentException('Data statistik tidak boleh kosong.');
        }

        $values = array_map('floatval', array_values($values));
        $count = count($values);
        $average = array_sum($values) / $count;
        $sumSquares = array_sum(array_map(fn (float $value): float => ($value - $average) ** 2, $values));
        $sampleStandardDeviation = $count > 1 ? sqrt($sumSquares / ($count - 1)) : null;

        return [
            'count' => $count,
            'average' => $average,
            'minimum' => min($values),
            'maximum' => max($values),
            'range' => max($values) - min($values),
            'sample_standard_deviation' => $sampleStandardDeviation,
            'coefficient_of_variation_percent' => $sampleStandardDeviation !== null && $average != 0.0
                ? $sampleStandardDeviation / abs($average) * 100
                : null,
        ];
    }
}
