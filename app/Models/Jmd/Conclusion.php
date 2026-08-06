<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conclusion extends ProjectRecord
{
    use SoftDeletes;

    protected $table = 'jmd_conclusions';

    protected function casts(): array
    {
        return ['generation_snapshot' => 'array', 'edited_at' => 'datetime', 'approved_at' => 'datetime'];
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(Revision::class, 'jmd_revision_id')->withTrashed();
    }
}
