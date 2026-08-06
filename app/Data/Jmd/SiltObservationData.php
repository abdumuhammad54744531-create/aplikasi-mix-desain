<?php

namespace App\Data\Jmd;

final readonly class SiltObservationData
{
    public function __construct(
        public float $containerMass,
        public float $beforeWashContainerMass,
        public float $afterWashContainerMass,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (float) $data['container_mass'],
            (float) $data['before_wash_container_mass'],
            (float) $data['after_wash_container_mass'],
        );
    }
}
