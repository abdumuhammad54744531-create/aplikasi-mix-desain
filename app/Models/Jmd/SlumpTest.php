<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlumpTest extends ProjectRecord
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'measured_at' => 'datetime', 'has_segregation' => 'boolean',
            'has_bleeding' => 'boolean', 'adjustment_snapshot' => 'array',
        ];
    }

    public function trialMix(): BelongsTo
    {
        return $this->belongsTo(TrialMix::class);
    }
}
