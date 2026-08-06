<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\HasMany;

class SiltTest extends TestRecord
{
    public function items(): HasMany
    {
        return $this->hasMany(SiltTestItem::class)->orderBy('observation_number');
    }
}
