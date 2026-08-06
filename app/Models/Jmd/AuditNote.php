<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditNote extends ProjectRecord
{
    protected $table = 'jmd_audit_notes';

    protected function casts(): array
    {
        return [
            'report_value' => 'array', 'application_value' => 'array',
            'difference_value' => 'array', 'decided_at' => 'datetime',
        ];
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(Revision::class, 'jmd_revision_id')->withTrashed();
    }
}
