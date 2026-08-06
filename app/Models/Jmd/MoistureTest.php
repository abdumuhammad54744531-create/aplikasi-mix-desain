<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\HasMany;

class MoistureTest extends TestRecord
{
    public function items(): HasMany
    {
        return $this->hasMany(MoistureTestItem::class)->orderBy('observation_number');
    }
}
