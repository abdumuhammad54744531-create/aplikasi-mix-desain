<?php

namespace App\Data\Jmd;

final readonly class BulkDensityObservationData
{
    public function __construct(
        public string $condition,
        public float $mouldVolumeCm3,
        public float $mouldMass,
        public float $filledMouldMass,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['condition'],
            (float) $data['mould_volume_cm3'],
            (float) $data['mould_mass'],
            (float) $data['filled_mould_mass'],
        );
    }
}
