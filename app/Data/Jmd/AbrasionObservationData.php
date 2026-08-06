<?php

namespace App\Data\Jmd;

final readonly class AbrasionObservationData
{
    public function __construct(
        public float $initialMass,
        public float $retainedNo12Mass,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self((float) $data['initial_mass'], (float) $data['retained_no12_mass']);
    }
}
