<?php

namespace App\Models\Jmd;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualOverride extends ProjectRecord
{
    protected $table = 'jmd_manual_overrides';

    protected function casts(): array
    {
        return [
            'original_value' => 'array', 'override_value' => 'array',
            'overridden_at' => 'datetime', 'approved_at' => 'datetime',
        ];
    }

    public function overriddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }
}
