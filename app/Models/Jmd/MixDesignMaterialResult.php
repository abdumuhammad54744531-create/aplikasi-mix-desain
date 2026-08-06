<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MixDesignMaterialResult extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['calculation_snapshot' => 'array'];
    }

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(MixDesignCalculation::class, 'mix_design_calculation_id');
    }
}
