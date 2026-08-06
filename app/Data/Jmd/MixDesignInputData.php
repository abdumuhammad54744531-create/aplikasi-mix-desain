<?php

namespace App\Data\Jmd;

final readonly class MixDesignInputData
{
    public function __construct(
        public float $specifiedStrengthMpa,
        public float $statisticalFactorK,
        public float $standardDeviationMpa,
        public float $strengthWaterCementRatio,
        public float $durabilityMaximumWaterCementRatio,
        public float $waterContentKg,
        public float $minimumCementKg,
        public ?float $maximumCementKg,
        public float $cementSpecificGravity,
        public float $fineAggregateSpecificGravity,
        public float $coarseAggregateSpecificGravity,
        public float $coarseAggregateBulkDensityKgM3,
        public float $coarseAggregateVolumeFactor,
        public float $airContentPercent = 0,
        public float $admixtureMassKg = 0,
        public ?float $admixtureSpecificGravity = null,
        public ?float $manualWaterCementRatio = null,
        public ?string $manualOverrideReason = null,
    ) {}
}
