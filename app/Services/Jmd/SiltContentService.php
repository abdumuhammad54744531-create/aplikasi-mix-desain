<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;
use App\Data\Jmd\SiltObservationData;
use InvalidArgumentException;

final readonly class SiltContentService
{
    public function __construct(private Statistics $statistics) {}

    /** @param  array<SiltObservationData>  $observations */
    public function calculate(array $observations, float $limitPercent, string $source): CalculationResult
    {
        if (count($observations) < 2) {
            throw new InvalidArgumentException('Minimal dua observasi kadar lumpur diperlukan.');
        }
        if ($limitPercent < 0) {
            throw new InvalidArgumentException('Batas kadar lumpur tidak boleh negatif.');
        }

        $rows = [];
        $values = [];
        foreach ($observations as $observation) {
            $before = $observation->beforeWashContainerMass - $observation->containerMass;
            $after = $observation->afterWashContainerMass - $observation->containerMass;
            if ($before <= 0 || $after <= 0) {
                throw new InvalidArgumentException('Massa benda uji sebelum dan setelah pencucian harus lebih besar dari nol.');
            }
            if ($after > $before) {
                throw new InvalidArgumentException('Massa setelah pencucian tidak boleh lebih besar dari massa sebelum pencucian.');
            }
            $silt = ($before - $after) / $before * 100;
            $rows[] = compact('before', 'after', 'silt');
            $values[] = $silt;
        }
        $statistics = $this->statistics->summarize($values);
        $status = $statistics['average'] < $limitPercent ? 'meets' : 'does_not_meet';

        return CalculationResult::fromRaw(
            raw: ['observations' => $rows, 'statistics' => $statistics, 'limit_percent' => $limitPercent, 'status' => $status],
            units: ['before' => 'g', 'after' => 'g', 'silt' => '%', 'limit_percent' => '%'],
            formulae: ['before' => 'Bsebelum = (cawan + sampel sebelum) - cawan', 'after' => 'Bsesudah = (cawan + sampel sesudah) - cawan', 'silt' => 'KL = ((Bsebelum - Bsesudah) / Bsebelum) x 100%'],
            sources: ['limit_percent' => $source],
            log: ["Rata-rata {$statistics['average']}% dibandingkan dengan batas {$limitPercent}%: {$status}."],
        );
    }
}
