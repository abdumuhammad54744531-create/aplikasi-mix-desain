<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\HasMany;

class SieveTest extends TestRecord
{
    public function items(): HasMany
    {
        return $this->hasMany(SieveTestItem::class)->orderBy('sort_order');
    }
}
