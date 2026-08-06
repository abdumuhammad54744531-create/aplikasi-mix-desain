<?php

namespace App\Data\Jmd;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

final readonly class CalculationResult implements Arrayable, JsonSerializable
{
    public function __construct(
        public array $raw,
        public array $rounded,
        public array $units,
        public array $formulae,
        public array $sources = [],
        public array $messages = [],
        public array $log = [],
        public bool $valid = true,
    ) {}

    public static function fromRaw(
        array $raw,
        array $units,
        array $formulae,
        array $sources = [],
        array $messages = [],
        array $log = [],
        bool $valid = true,
        int $precision = 6,
    ): self {
        return new self(
            raw: $raw,
            rounded: self::roundForDisplay($raw, $precision),
            units: $units,
            formulae: $formulae,
            sources: $sources,
            messages: $messages,
            log: $log,
            valid: $valid,
        );
    }

    public function toArray(): array
    {
        return [
            'raw' => $this->raw,
            'rounded' => $this->rounded,
            'units' => $this->units,
            'formulae' => $this->formulae,
            'sources' => $this->sources,
            'messages' => $this->messages,
            'log' => $this->log,
            'valid' => $this->valid,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private static function roundForDisplay(array $values, int $precision): array
    {
        return array_map(
            fn (mixed $value): mixed => is_array($value)
                ? self::roundForDisplay($value, $precision)
                : (is_float($value) ? round($value, $precision) : $value),
            $values,
        );
    }
}
