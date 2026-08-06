<?php

namespace App\Models\Jmd;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class TestRecord extends ProjectRecord
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'tested_at' => 'date',
            'approved_at' => 'datetime',
            'result_snapshot' => 'array',
            'standard_snapshot' => 'array',
        ];
    }

    public function projectMaterial(): BelongsTo
    {
        return $this->belongsTo(ProjectMaterial::class, 'jmd_project_material_id')->withTrashed();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
