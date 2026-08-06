<?php

namespace App\Models\Jmd;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompressiveStrengthTest extends ProjectRecord
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'age_factor_snapshot' => 'array', 'statistics_snapshot' => 'array',
            'validation_messages' => 'array', 'approved_at' => 'datetime',
        ];
    }

    public function trialMix(): BelongsTo
    {
        return $this->belongsTo(TrialMix::class);
    }

    public function specimens(): HasMany
    {
        return $this->hasMany(CompressiveStrengthSpecimen::class)->orderBy('tested_at');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
