<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrialMixMaterial extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['weighed' => 'boolean', 'weighed_at' => 'datetime'];
    }

    public function trialMix(): BelongsTo
    {
        return $this->belongsTo(TrialMix::class);
    }
}
