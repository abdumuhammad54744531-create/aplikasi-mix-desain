<?php

namespace App\Data\Jmd;

use App\Enums\SpecimenType;

final readonly class TrialMixData
{
    public function __construct(
        public SpecimenType $specimenType,
        public int $specimenCount,
        public float $wasteFactor,
        public float $slumpVolumeM3,
        public float $manualExtraVolumeM3,
        public ?float $diameterMm = null,
        public ?float $heightMm = null,
        public ?float $lengthMm = null,
        public ?float $widthMm = null,
        public ?float $manualSpecimenVolumeM3 = null,
    ) {}
}
