<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CementSpecificGravityTest extends TestRecord
{
    protected $table = 'cement_sg_tests';

    public function items(): HasMany
    {
        return $this->hasMany(CementSpecificGravityItem::class, 'cement_sg_test_id')->orderBy('observation_number');
    }
}
