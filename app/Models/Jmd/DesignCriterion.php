<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\SoftDeletes;

class DesignCriterion extends ProjectRecord
{
    use SoftDeletes;

    protected $table = 'jmd_design_criteria';

    protected function casts(): array
    {
        return [
            'uses_admixture' => 'boolean',
            'standard_snapshot' => 'array',
        ];
    }
}
