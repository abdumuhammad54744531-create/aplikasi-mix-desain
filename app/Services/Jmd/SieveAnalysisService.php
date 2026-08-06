<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;
use App\Enums\AggregateType;
use InvalidArgumentException;

final class SieveAnalysisService
{
    public function calculate(
        AggregateType $aggregateType,
        float $initialMass,
        array $sieves,
        float $lossTolerancePercent,
        string $source,
    ): CalculationResult {
        if ($initialMass <= 0 || $sieves === []) {
            throw new InvalidArgumentException('Massa awal dan daftar saringan harus tersedia.');
        }

        usort($sieves, fn (array $left, array $right): int => ($right['sieve_size_mm'] ?? -1) <=> ($left['sieve_size_mm'] ?? -1));
        $totalRetained = array_sum(array_map(fn (array $row): float => (float) $row['retained_mass'], $sieves));
        $difference = $initialMass - $totalRetained;
        $lossPercent = abs($difference) / $initialMass * 100;
        $cumulative = 0.0;
        $rows = [];
        $outOfBounds = [];
        $finenessModulusSum = 0.0;
        $fmSizes = [4.75, 2.36, 1.18, 0.60, 0.30, 0.15];

        foreach ($sieves as $index => $sieve) {
            $retained = (float) $sieve['retained_mass'];
            if ($retained < 0) {
                throw new InvalidArgumentException('Massa tertahan tidak boleh negatif.');
            }
            $retainedPercent = $retained / $initialMass * 100;
            $cumulative += $retainedPercent;
            $passing = 100 - $cumulative;
            $lower = isset($sieve['lower_limit_percent']) ? (float) $sieve['lower_limit_percent'] : null;
            $upper = isset($sieve['upper_limit_percent']) ? (float) $sieve['upper_limit_percent'] : null;
            $deviation = 0.0;
            if ($lower !== null && $passing < $lower) {
                $deviation = $passing - $lower;
            } elseif ($upper !== null && $passing > $upper) {
                $deviation = $passing - $upper;
            }
            if ($deviation != 0.0) {
                $outOfBounds[] = ['index' => $index, 'sieve_size_mm' => $sieve['sieve_size_mm'], 'deviation_percent' => $deviation];
            }
            $size = isset($sieve['sieve_size_mm']) ? (float) $sieve['sieve_size_mm'] : null;
            if ($aggregateType === AggregateType::Fine && $size !== null && $this->containsSize($fmSizes, $size)) {
                $finenessModulusSum += $cumulative;
            }
            $rows[] = [
                'sieve_size_mm' => $size, 'is_pan' => (bool) ($sieve['is_pan'] ?? false),
                'retained_mass' => $retained, 'retained_percent' => $retainedPercent,
                'cumulative_retained_percent' => $cumulative, 'passing_percent' => $passing,
                'lower_limit_percent' => $lower, 'upper_limit_percent' => $upper,
                'deviation_percent' => $deviation,
            ];
        }

        [$maximumSize, $nominalMaximumSize] = $this->aggregateSizes($rows);
        $massBalanceStatus = $lossPercent <= $lossTolerancePercent ? 'meets' : 'does_not_meet';
        $gradationStatus = $outOfBounds === [] ? 'meets' : 'not_fully_meets';

        return CalculationResult::fromRaw(
            raw: [
                'rows' => $rows, 'initial_mass' => $initialMass, 'total_retained_mass' => $totalRetained,
                'mass_difference' => $difference, 'loss_percent' => $lossPercent,
                'loss_tolerance_percent' => $lossTolerancePercent, 'mass_balance_status' => $massBalanceStatus,
                'fineness_modulus' => $aggregateType === AggregateType::Fine ? $finenessModulusSum / 100 : null,
                'maximum_size_mm' => $maximumSize, 'nominal_maximum_size_mm' => $nominalMaximumSize,
                'gradation_status' => $gradationStatus, 'out_of_bounds' => $outOfBounds,
            ],
            units: ['mass' => 'g', 'percent' => '%', 'sieve_size_mm' => 'mm'],
            formulae: [
                'retained_percent' => '(massa tertahan / massa awal) x 100%',
                'cumulative_retained_percent' => 'jumlah berjalan persen tertahan',
                'passing_percent' => '100% - kumulatif tertahan',
                'loss_percent' => '|massa awal - total tertahan| / massa awal x 100%',
                'fineness_modulus' => 'jumlah kumulatif tertahan saringan standar / 100',
            ],
            sources: ['gradation_limits' => $source, 'loss_tolerance' => $source],
            messages: $outOfBounds === [] ? [] : ['Kurva tidak sepenuhnya berada di dalam batas gradasi.'],
            log: ["Neraca massa: {$totalRetained} dari {$initialMass}; kehilangan {$lossPercent}%."],
            valid: $massBalanceStatus === 'meets',
        );
    }

    private function aggregateSizes(array $rows): array
    {
        $nonPan = array_values(array_filter($rows, fn (array $row): bool => ! $row['is_pan'] && $row['sieve_size_mm'] !== null));
        $maximum = null;
        foreach (array_reverse($nonPan) as $row) {
            if ($row['passing_percent'] >= 99.999999) {
                $maximum = $row['sieve_size_mm'];
                break;
            }
        }

        $firstAboveTenRetained = null;
        foreach ($nonPan as $index => $row) {
            if ($row['retained_percent'] > 10) {
                $firstAboveTenRetained = $index;
                break;
            }
        }
        $nominal = $firstAboveTenRetained === null
            ? $maximum
            : ($nonPan[max(0, $firstAboveTenRetained - 1)]['sieve_size_mm'] ?? $maximum);

        return [$maximum, $nominal];
    }

    private function containsSize(array $sizes, float $value): bool
    {
        return array_any($sizes, fn (float $size): bool => abs($size - $value) < 0.000001);
    }
}
