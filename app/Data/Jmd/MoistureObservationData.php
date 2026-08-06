<?php

namespace App\Data\Jmd;

final readonly class MoistureObservationData
{
    public function __construct(
        public float $containerMass,
        public float $wetContainerMass,
        public float $dryContainerMass,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (float) $data['container_mass'],
            (float) $data['wet_container_mass'],
            (float) $data['dry_container_mass'],
        );
    }
}
