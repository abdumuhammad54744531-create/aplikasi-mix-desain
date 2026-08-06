<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoistureCorrection extends ProjectRecord
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['calculation_snapshot' => 'array', 'validation_messages' => 'array'];
    }

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(MixDesignCalculation::class, 'mix_design_calculation_id');
    }
}
