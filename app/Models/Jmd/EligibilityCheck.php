<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EligibilityCheck extends ProjectRecord
{
    protected $table = 'jmd_eligibility_checks';

    protected function casts(): array
    {
        return ['actual_value' => 'array', 'required_value' => 'array'];
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(Revision::class, 'jmd_revision_id')->withTrashed();
    }
}
