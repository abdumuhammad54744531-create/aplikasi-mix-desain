<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkDensityTest extends TestRecord
{
    public function items(): HasMany
    {
        return $this->hasMany(BulkDensityItem::class)->orderBy('observation_number');
    }
}
