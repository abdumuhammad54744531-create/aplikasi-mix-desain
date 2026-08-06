<?php

namespace App\Models\Jmd;

use App\Models\MaterialSource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectMaterial extends ProjectRecord
{
    use SoftDeletes;

    protected $table = 'jmd_project_materials';

    protected function casts(): array
    {
        return ['use_test_result' => 'boolean', 'properties' => 'array'];
    }

    public function materialSource(): BelongsTo
    {
        return $this->belongsTo(MaterialSource::class)->withTrashed();
    }
}
