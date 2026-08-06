<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldBatchConversion extends ProjectRecord
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['result_snapshot' => 'array'];
    }

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(MixDesignCalculation::class, 'mix_design_calculation_id');
    }
}
