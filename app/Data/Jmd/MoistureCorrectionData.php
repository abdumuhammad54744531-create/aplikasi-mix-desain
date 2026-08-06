<?php

namespace App\Data\Jmd;

final readonly class MoistureCorrectionData
{
    public function __construct(
        public float $fineSsdMassKg,
        public float $fineMoisturePercent,
        public float $fineAbsorptionPercent,
        public float $coarseSsdMassKg,
        public float $coarseMoisturePercent,
        public float $coarseAbsorptionPercent,
        public float $designWaterKg,
        public float $cementMassKg,
    ) {}
}
