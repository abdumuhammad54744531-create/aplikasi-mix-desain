<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrialMix extends ProjectRecord
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['mixed_at' => 'datetime', 'calculation_snapshot' => 'array'];
    }

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(MixDesignCalculation::class, 'mix_design_calculation_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(TrialMixMaterial::class);
    }

    public function slumpTests(): HasMany
    {
        return $this->hasMany(SlumpTest::class);
    }
}
