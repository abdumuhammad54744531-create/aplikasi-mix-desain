<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbrasionTestItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['calculation_snapshot' => 'array'];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(AbrasionTest::class, 'abrasion_test_id');
    }
}
