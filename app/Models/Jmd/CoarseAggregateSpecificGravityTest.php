<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CoarseAggregateSpecificGravityTest extends TestRecord
{
    protected $table = 'coarse_aggregate_sg_tests';

    public function items(): HasMany
    {
        return $this->hasMany(CoarseAggregateSpecificGravityItem::class, 'coarse_aggregate_sg_test_id')->orderBy('observation_number');
    }
}
