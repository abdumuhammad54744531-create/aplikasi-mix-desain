<?php

namespace App\Services\Jmd;

use App\Data\Jmd\CalculationResult;

final class JmdValidationService
{
    public function evaluate(array $checks): CalculationResult
    {
        $rows = [];
        foreach ($checks as $key => $check) {
            $available = (bool) ($check['available'] ?? false);
            $meets = $available ? (bool) ($check['meets'] ?? false) : null;
            $rows[$key] = [
                'available' => $available,
                'actual' => $check['actual'] ?? null,
                'required' => $check['required'] ?? null,
                'status' => ! $available ? 'not_tested' : ($meets ? 'meets' : 'does_not_meet'),
                'message' => $check['message'] ?? null,
                'source' => $check['source'] ?? null,
            ];
        }

        $statuses = array_column($rows, 'status');
        $overall = in_array('does_not_meet', $statuses, true)
            ? 'does_not_meet'
            : (in_array('not_tested', $statuses, true) ? 'needs_verification' : 'meets');

        return CalculationResult::fromRaw(
            raw: ['checks' => $rows, 'overall_status' => $overall, 'complete' => ! in_array('not_tested', $statuses, true)],
            units: [],
            formulae: ['overall_status' => 'merah jika satu kriteria gagal; kuning jika ada yang belum diuji; hijau jika seluruhnya tersedia dan memenuhi'],
            sources: array_filter(array_map(fn (array $row): mixed => $row['source'], $rows)),
            messages: $overall === 'meets' ? [] : ['Status akhir memerlukan perhatian pada kriteria yang belum diuji atau tidak memenuhi.'],
            valid: $overall === 'meets',
        );
    }
}
