<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoarseAggregateSpecificGravityItem extends Model
{
    protected $table = 'coarse_aggregate_sg_items';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['calculation_snapshot' => 'array'];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(CoarseAggregateSpecificGravityTest::class, 'coarse_aggregate_sg_test_id');
    }
}
