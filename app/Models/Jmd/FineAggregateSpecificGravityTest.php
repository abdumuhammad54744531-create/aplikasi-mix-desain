<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\HasMany;

class FineAggregateSpecificGravityTest extends TestRecord
{
    protected $table = 'fine_aggregate_sg_tests';

    public function items(): HasMany
    {
        return $this->hasMany(FineAggregateSpecificGravityItem::class, 'fine_aggregate_sg_test_id')->orderBy('observation_number');
    }
}
