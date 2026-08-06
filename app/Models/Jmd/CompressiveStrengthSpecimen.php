<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompressiveStrengthSpecimen extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['cast_at' => 'date', 'tested_at' => 'date', 'calculation_snapshot' => 'array'];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(CompressiveStrengthTest::class, 'compressive_strength_test_id');
    }
}
