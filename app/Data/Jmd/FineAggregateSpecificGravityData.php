<?php

namespace App\Data\Jmd;

final readonly class FineAggregateSpecificGravityData
{
    public function __construct(
        public float $pycnometerMass,
        public float $ssdSampleMass,
        public float $pycnometerSampleWaterMass,
        public float $pycnometerWaterMass,
        public float $ovenDrySampleMass,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (float) $data['pycnometer_mass'],
            (float) $data['ssd_sample_mass'],
            (float) $data['pycnometer_sample_water_mass'],
            (float) $data['pycnometer_water_mass'],
            (float) $data['oven_dry_sample_mass'],
        );
    }
}
