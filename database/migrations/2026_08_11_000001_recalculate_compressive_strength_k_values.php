<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->recalculate(fn (float $mpa): float => $mpa * 10 / 0.83, 'Perkiraan 28 hari × 10 ÷ 0,83');
    }

    public function down(): void
    {
        $this->recalculate(fn (float $mpa): float => $mpa * 10.19716213, 'Perkiraan 28 hari × 10,19716213');
    }

    private function recalculate(callable $convert, string $formula): void
    {
        DB::table('laboratory_workflows')
            ->where('type', 'compressive-strength')
            ->orderBy('id')
            ->chunkById(100, function ($workflows) use ($convert, $formula): void {
                foreach ($workflows as $workflow) {
                    $result = json_decode((string) $workflow->result_data, true);

                    if (! is_array($result)) {
                        continue;
                    }

                    foreach (($result['detail_rows'] ?? []) as $index => $row) {
                        if (is_array($row) && is_numeric($row['estimated_28_mpa'] ?? null)) {
                            $result['detail_rows'][$index]['estimated_k_kgcm2'] = $convert((float) $row['estimated_28_mpa']);
                        }
                    }

                    if (is_numeric($result['Kuat tekan karakteristik (MPa)'] ?? null)) {
                        $result['Mutu karakteristik (kg/cm²)'] = $convert((float) $result['Kuat tekan karakteristik (MPa)']);
                    }

                    $result['Rumus Mutu K'] = $formula;

                    DB::table('laboratory_workflows')->where('id', $workflow->id)->update([
                        'result_data' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION),
                    ]);
                }
            });
    }
};
