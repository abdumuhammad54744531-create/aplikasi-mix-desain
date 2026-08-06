<?php

namespace App\Data\Jmd;

use App\Enums\SpecimenType;

final readonly class CompressiveStrengthSpecimenData
{
    public function __construct(
        public string $number,
        public SpecimenType $type,
        public float $maximumLoad,
        public string $loadUnit,
        public int $ageDays,
        public float $ageFactor,
        public ?float $diameterMm = null,
        public ?float $lengthMm = null,
        public ?float $widthMm = null,
    ) {}
}
