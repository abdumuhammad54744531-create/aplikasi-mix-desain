<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revision extends ProjectRecord
{
    use SoftDeletes;

    protected $table = 'jmd_revisions';

    protected function casts(): array
    {
        return [
            'calculation_snapshot' => 'array', 'standard_snapshot' => 'array',
            'report_snapshot' => 'array', 'approved_at' => 'datetime', 'locked_at' => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_revision_id');
    }

    public function eligibilityChecks(): HasMany
    {
        return $this->hasMany(EligibilityCheck::class, 'jmd_revision_id');
    }
}
