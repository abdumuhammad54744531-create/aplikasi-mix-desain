<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SieveTestItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_pan' => 'boolean', 'calculation_snapshot' => 'array'];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(SieveTest::class, 'sieve_test_id');
    }
}
