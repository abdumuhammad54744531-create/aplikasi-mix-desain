<?php

namespace App\Services;

use App\Models\AggregateTestRun;
use App\Models\CoarseAggregateTest;
use App\Models\FineAggregateTest;
use App\Models\MaterialSource;

final class AggregateTestSummaryService
{
    public function sync(int $projectId, ?int $materialSourceId, string $aggregate): FineAggregateTest|CoarseAggregateTest
    {
        $model = $aggregate === 'fine' ? FineAggregateTest::class : CoarseAggregateTest::class;
        $source = $materialSourceId ? MaterialSource::find($materialSourceId) : null;
        $runs = AggregateTestRun::query()
            ->where('project_id', $projectId)
            ->where('aggregate_type', $aggregate)
            ->when($materialSourceId, fn ($query) => $query->where('material_source_id', $materialSourceId), fn ($query) => $query->whereNull('material_source_id'))
            ->latest()
            ->get()
            ->unique('test_type')
            ->keyBy('test_type');

        $summary = $model::query()
            ->where('project_id', $projectId)
            ->when($materialSourceId, fn ($query) => $query->where('material_source_id', $materialSourceId), fn ($query) => $query->whereNull('material_source_id'))
            ->latest()
            ->first();

        $latestRun = $runs->sortByDesc('tested_at')->first();
        $attributes = [
            'project_id' => $projectId,
            'material_source_id' => $materialSourceId,
            'sample_number' => $latestRun?->sample_number ?? $source?->sample_number ?? '-',
            'received_at' => $latestRun?->tested_at,
            'tested_at' => $latestRun?->tested_at ?? now()->toDateString(),
            'technician' => $latestRun?->technician ?? auth()->user()?->name ?? '-',
            'notes' => $latestRun?->notes,
            'updated_by' => auth()->id(),
        ];

        $this->mapRun($attributes, $runs->get('moisture'), ['moisture' => 'field_moisture']);
        $this->mapRun($attributes, $runs->get('silt'), ['silt' => 'silt_content']);
        $this->mapRun($attributes, $runs->get('specific-gravity'), [
            'bulk_dry' => 'bulk_specific_gravity_dry',
            'bulk_ssd' => 'specific_gravity_ssd',
            'apparent' => 'apparent_specific_gravity',
            'absorption' => 'absorption',
        ]);
        $this->mapRun($attributes, $runs->get('bulk-density'), [
            'bulk_density' => 'compacted_bulk_density',
            'voids' => 'void_percentage',
        ]);

        if ($aggregate === 'fine') {
            $this->mapRun($attributes, $runs->get('sieve'), ['fineness_modulus' => 'fineness_modulus']);
            if ($sieve = $runs->get('sieve')) {
                $zone = data_get($sieve->observations, '0.selected_zone');
                $attributes['gradation_zone'] = in_array((string) $zone, ['1', '2', '3', '4'], true) ? 'Zona '.$zone : null;
            }
            $attributes['quarry'] = $source?->quarry ?? $source?->producer;
            $attributes['supplier'] = $source?->supplier;
        } else {
            $this->mapRun($attributes, $runs->get('los-angeles'), ['abrasion' => 'abrasion']);
            if ($sieve = $runs->get('sieve')) {
                $attributes['nominal_maximum_size'] = $this->nominalMaximumSize($sieve);
            }
            $attributes['aggregate_type'] = $source?->type ?? 'Kerikil';
            $attributes['quarry'] = $source?->quarry ?? $source?->producer;
        }

        if ($summary) {
            $summary->update($attributes);
            return $summary->fresh();
        }

        return $model::create($attributes + [
            'test_number' => strtoupper(substr($aggregate, 0, 3)).'-'.$projectId.'-'.($materialSourceId ? 'SRC-'.$materialSourceId : 'UMUM'),
            'created_by' => auth()->id(),
        ]);
    }

    private function mapRun(array &$attributes, ?AggregateTestRun $run, array $mapping): void
    {
        if (! $run) {
            return;
        }

        foreach ($mapping as $result => $column) {
            $attributes[$column] = data_get($run->results, 'averages.'.$result);
        }
    }

    private function nominalMaximumSize(AggregateTestRun $run): ?float
    {
        $observation = data_get($run->observations, '0', []);
        $sampleMass = (float) ($observation['sample_mass'] ?? 0);
        if ($sampleMass <= 0) {
            return null;
        }

        $sieves = ['r750' => 75.0, 'r375' => 37.5, 'r190' => 19.0, 'r095' => 9.5, 'r475' => 4.75];
        $sizes = array_values($sieves);
        foreach (array_keys($sieves) as $index => $key) {
            $retainedPercent = (float) ($observation[$key] ?? 0) / $sampleMass * 100;
            if ($retainedPercent > 10) {
                return $sizes[max(0, $index - 1)];
            }
        }

        $selected = (string) ($observation['selected_zone'] ?? '');
        return ['1' => 9.5, '2' => 19.0, '3' => 37.5][$selected] ?? null;
    }
}
