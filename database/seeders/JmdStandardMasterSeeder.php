<?php

namespace Database\Seeders;

use App\Models\StandardReference;
use App\Models\StandardTableHeader;
use App\Services\Jmd\StandardMasterService;
use Illuminate\Database\Seeder;

class JmdStandardMasterSeeder extends Seeder
{
    public function run(): void
    {
        $reference = StandardReference::firstOrCreate([
            'standard_number' => 'JMD-CONFIG', 'standard_year' => '2026', 'revision_number' => 1,
        ], [
            'name' => 'Konfigurasi Awal Master JMD — Wajib Diverifikasi',
            'effective_at' => today()->toDateString(), 'is_active' => true,
            'description' => 'Struktur dan nilai awal dari spesifikasi aplikasi. Bukan pengganti dokumen standar resmi; administrator laboratorium wajib memverifikasi sebelum penggunaan produksi.',
        ]);

        foreach (StandardMasterService::CATALOG as $key => $name) {
            StandardTableHeader::firstOrCreate([
                'standard_reference_id' => $reference->id, 'key' => $key, 'revision_number' => 1,
            ], [
                'name' => $name, 'unit' => $this->unit($key), 'dimensions' => $this->dimensions($key),
                'notes' => 'Master awal; lengkapi dan verifikasi terhadap dokumen resmi yang berlaku.', 'is_active' => true,
            ]);
        }

        $this->values($reference);
    }

    private function values(StandardReference $reference): void
    {
        $this->upsert($reference, 'silt_limits', 'fine', 5, '%', ['aggregate_type' => 'fine', 'comparison' => '<']);
        $this->upsert($reference, 'silt_limits', 'coarse', 1, '%', ['aggregate_type' => 'coarse', 'comparison' => '<']);
        $this->upsert($reference, 'abrasion_limits', 'coarse', 40, '%', ['aggregate_type' => 'coarse', 'comparison' => '<=']);
        $this->upsert($reference, 'sieve_loss_tolerance', 'default', 1, '%', ['scope' => 'mass_balance'], 'Nilai konfigurasi awal yang dapat direvisi administrator.');

        $fine = [['9.5', 9.5], ['4.75', 4.75], ['2.36', 2.36], ['1.18', 1.18], ['0.60', 0.60], ['0.30', 0.30], ['0.15', 0.15], ['Pan', null]];
        $coarse = [['75', 75], ['63.5', 63.5], ['50.8', 50.8], ['37.5', 37.5], ['25.4', 25.4], ['19', 19], ['12.5', 12.5], ['9.5', 9.5], ['4.75', 4.75], ['2.36', 2.36], ['Pan', null]];
        foreach (['fine' => $fine, 'coarse' => $coarse] as $type => $sizes) {
            foreach ($sizes as $index => [$label, $size]) {
                $this->upsert($reference, 'sieve_sizes', $type, $size, 'mm', [
                    'aggregate_type' => $type, 'label' => $label, 'is_pan' => $size === null,
                ], null, $type.'-'.$label, $index + 1);
            }
        }
    }

    private function upsert(StandardReference $reference, string $key, string $row, ?float $number, ?string $unit, array $dimensions, ?string $notes = null, ?string $column = null, int $sort = 0): void
    {
        $header = $reference->tables()->where('key', $key)->where('revision_number', 1)->firstOrFail();
        $header->values()->updateOrCreate(['row_key' => $row, 'column_key' => $column], [
            'dimension_values' => $dimensions, 'numeric_value' => $number, 'unit' => $unit,
            'notes' => $notes, 'sort_order' => $sort, 'is_active' => true,
        ]);
    }

    private function unit(string $key): ?string
    {
        return match ($key) {
            'durability_max_wcr', 'margin_value', 'specimen_factor', 'trial_mix_correction' => null,
            'free_water_content', 'fresh_concrete_density' => 'kg/m³',
            'silt_limits', 'abrasion_limits', 'absorption_limits', 'sieve_loss_tolerance', 'aggregate_proportion' => '%',
            'sieve_sizes' => 'mm', 'concrete_age_factor' => 'factor', 'standard_deviation' => 'MPa',
            default => null,
        };
    }

    private function dimensions(string $key): array
    {
        return match ($key) {
            'silt_limits', 'abrasion_limits', 'absorption_limits' => ['material_type', 'comparison'],
            'sieve_sizes' => ['aggregate_type', 'label', 'is_pan'],
            'fine_gradation_limits', 'coarse_gradation_limits' => ['gradation', 'sieve_size_mm'],
            'free_water_content' => ['slump', 'maximum_size_mm', 'aggregate_shape'],
            default => [],
        };
    }
}
