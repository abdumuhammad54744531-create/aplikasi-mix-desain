<?php

namespace App\Data\Jmd;

final readonly class CoarseAggregateSpecificGravityData
{
    public function __construct(
        public float $ssdAirMass,
        public float $submergedMass,
        public float $ovenDryMass,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (float) $data['ssd_air_mass'],
            (float) $data['submerged_mass'],
            (float) $data['oven_dry_mass'],
        );
    }
}
