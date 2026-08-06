<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AbrasionTest extends TestRecord
{
    public function items(): HasMany
    {
        return $this->hasMany(AbrasionTestItem::class)->orderBy('observation_number');
    }
}
