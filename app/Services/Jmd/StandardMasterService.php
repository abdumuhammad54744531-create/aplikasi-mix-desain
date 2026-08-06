<?php

namespace App\Services\Jmd;

use App\Models\StandardReference;
use App\Models\StandardTableHeader;
use App\Models\StandardTableValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class StandardMasterService
{
    public const CATALOG = [
        'strength_wcr_relationship' => 'Hubungan kuat tekan dan FAS',
        'durability_max_wcr' => 'Batas FAS maksimum berdasarkan durabilitas',
        'free_water_content' => 'Kadar air bebas',
        'coarse_aggregate_volume' => 'Volume agregat kasar',
        'fresh_concrete_density' => 'Perkiraan berat isi beton segar',
        'aggregate_proportion' => 'Persentase agregat halus dan kasar',
        'fine_gradation_limits' => 'Batas gradasi pasir',
        'coarse_gradation_limits' => 'Batas gradasi kerikil',
        'concrete_age_factor' => 'Faktor konversi umur beton',
        'standard_deviation' => 'Standar deviasi',
        'margin_value' => 'Nilai margin',
        'specimen_factor' => 'Faktor benda uji',
        'trial_mix_correction' => 'Faktor koreksi trial mix',
        'silt_limits' => 'Batas kadar lumpur',
        'abrasion_limits' => 'Batas abrasi',
        'absorption_limits' => 'Batas penyerapan',
        'sieve_loss_tolerance' => 'Toleransi kehilangan analisa saringan',
        'sieve_sizes' => 'Daftar ukuran saringan',
        'concrete_strength_catalog' => 'Daftar mutu beton K dan f’c',
        'sni_7656_water_demand' => 'Kebutuhan air menurut SNI 7656:2012',
    ];

    public function activeTables(?array $keys = null): Collection
    {
        return StandardTableHeader::query()
            ->with(['standard', 'values' => fn ($query) => $query->where('is_active', true)])
            ->where('is_active', true)
            ->whereHas('standard', fn ($query) => $query->where('is_active', true)
                ->where(fn ($dates) => $dates->whereNull('effective_at')->orWhereDate('effective_at', '<=', today()))
                ->where(fn ($dates) => $dates->whereNull('expires_at')->orWhereDate('expires_at', '>=', today())))
            ->when($keys, fn ($query) => $query->whereIn('key', $keys))
            ->orderBy('key')->get();
    }

    public function resolveValue(int $valueId, array $allowedKeys = []): array
    {
        $value = StandardTableValue::query()->with('header.standard')->where('is_active', true)->findOrFail($valueId);
        $header = $value->header;
        $standard = $header->standard;
        $outsideValidity = ($standard->effective_at && $standard->effective_at->isFuture())
            || ($standard->expires_at && $standard->expires_at->isPast());
        if (! $header->is_active || ! $standard->is_active || $outsideValidity || ($allowedKeys !== [] && ! in_array($header->key, $allowedKeys, true))) {
            throw ValidationException::withMessages(['standard_table_value_id' => 'Nilai master tidak aktif atau tidak sesuai dengan modul ini.']);
        }

        return [
            'mode' => 'table', 'reference_id' => $standard->id, 'table_header_id' => $header->id, 'value_id' => $value->id,
            'table_key' => $header->key, 'row_key' => $value->row_key, 'column_key' => $value->column_key,
            'dimension_values' => $value->dimension_values, 'numeric_value' => $value->numeric_value !== null ? (float) $value->numeric_value : null,
            'text_value' => $value->text_value, 'min_value' => $value->min_value !== null ? (float) $value->min_value : null,
            'max_value' => $value->max_value !== null ? (float) $value->max_value : null, 'unit' => $value->unit ?: $header->unit,
            'source' => $this->sourceLabel($standard), 'reference_revision' => $standard->revision_number,
            'table_revision' => $header->revision_number, 'captured_at' => now()->toIso8601String(),
        ];
    }

    public function reviseReference(StandardReference $current, array $attributes, ?int $userId): StandardReference
    {
        return DB::transaction(function () use ($current, $attributes, $userId) {
            $current->load('tables.values');
            $current->update(['is_active' => false, 'updated_by' => $userId]);
            $revision = StandardReference::create($attributes + [
                'revision_number' => $current->revision_number + 1, 'supersedes_id' => $current->id,
                'is_active' => true, 'created_by' => $userId, 'updated_by' => $userId,
            ]);
            foreach ($current->tables as $table) {
                $table->update(['is_active' => false, 'updated_by' => $userId]);
                $copy = $revision->tables()->create([
                    'key' => $table->key, 'name' => $table->name, 'unit' => $table->unit,
                    'dimensions' => $table->dimensions, 'notes' => $table->notes,
                    'is_active' => true, 'revision_number' => $table->revision_number + 1,
                    'created_by' => $userId, 'updated_by' => $userId,
                ]);
                foreach ($table->values as $value) {
                    $copy->values()->create($value->only([
                        'row_key', 'column_key', 'dimension_values', 'numeric_value', 'text_value',
                        'min_value', 'max_value', 'unit', 'notes', 'sort_order', 'is_active',
                    ]) + ['created_by' => $userId, 'updated_by' => $userId]);
                }
            }

            return $revision;
        });
    }

    public function reviseTable(StandardTableHeader $current, array $attributes, ?int $userId): StandardTableHeader
    {
        return DB::transaction(function () use ($current, $attributes, $userId) {
            $current->load('values')->update(['is_active' => false, 'updated_by' => $userId]);
            $copy = $current->standard->tables()->create($attributes + [
                'key' => $current->key, 'revision_number' => $current->revision_number + 1,
                'is_active' => true, 'created_by' => $userId, 'updated_by' => $userId,
            ]);
            foreach ($current->values as $value) {
                $copy->values()->create($value->only([
                    'row_key', 'column_key', 'dimension_values', 'numeric_value', 'text_value',
                    'min_value', 'max_value', 'unit', 'notes', 'sort_order', 'is_active',
                ]) + ['created_by' => $userId, 'updated_by' => $userId]);
            }

            return $copy;
        });
    }

    public function reviseValue(StandardTableValue $current, array $attributes, ?int $userId): StandardTableValue
    {
        return DB::transaction(function () use ($current, $attributes, $userId) {
            $header = $current->header;
            $index = $header->values()->get()->search(fn (StandardTableValue $value) => $value->is($current));
            $copy = $this->reviseTable($header, $header->only(['name', 'unit', 'dimensions', 'notes']), $userId);
            $value = $copy->values()->get()->get($index);
            abort_unless($value, 404);
            $value->update($attributes + ['updated_by' => $userId]);

            return $value;
        });
    }

    public function retireValue(StandardTableValue $current, ?int $userId): void
    {
        DB::transaction(function () use ($current, $userId) {
            $header = $current->header;
            $index = $header->values()->get()->search(fn (StandardTableValue $value) => $value->is($current));
            $copy = $this->reviseTable($header, $header->only(['name', 'unit', 'dimensions', 'notes']), $userId);
            $copy->values()->get()->get($index)?->delete();
        });
    }

    public function sourceLabel(StandardReference $standard): string
    {
        $identity = trim(implode(' ', array_filter([$standard->standard_number, $standard->standard_year])));

        return ($identity ? $identity.' — ' : '').$standard->name.' (Revisi '.$standard->revision_number.')';
    }
}
