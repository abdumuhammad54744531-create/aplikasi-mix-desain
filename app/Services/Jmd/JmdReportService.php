<?php

namespace App\Services\Jmd;

use JsonException;

final class JmdReportService
{
    /** @throws JsonException */
    public function snapshot(array $project, array $modules, array $standards, int $revision): array
    {
        $payload = $this->canonicalize([
            'project' => $project,
            'modules' => $modules,
            'standards' => $standards,
            'revision' => $revision,
        ]);
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);

        return ['payload' => $payload, 'hash' => hash('sha256', $json), 'algorithm' => 'sha256'];
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }
        if (array_is_list($value)) {
            return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
        }
        ksort($value, SORT_STRING);

        return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
    }
}
