<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FineAggregateSpecificGravityItem extends Model
{
    protected $table = 'fine_aggregate_sg_items';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['calculation_snapshot' => 'array'];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(FineAggregateSpecificGravityTest::class, 'fine_aggregate_sg_test_id');
    }
}
