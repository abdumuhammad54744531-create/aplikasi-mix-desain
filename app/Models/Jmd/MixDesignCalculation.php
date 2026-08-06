<?php

namespace App\Models\Jmd;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MixDesignCalculation extends ProjectRecord
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'input_snapshot' => 'array', 'standard_snapshot' => 'array',
            'raw_result' => 'array', 'rounded_result' => 'array',
            'calculation_log' => 'array', 'validation_messages' => 'array',
            'calculated_at' => 'datetime', 'locked_at' => 'datetime', 'approved_at' => 'datetime',
        ];
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(DesignCriterion::class, 'jmd_design_criteria_id')->withTrashed();
    }

    public function materials(): HasMany
    {
        return $this->hasMany(MixDesignMaterialResult::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
