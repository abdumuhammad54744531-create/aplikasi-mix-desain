<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardTableHeader extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['dimensions' => 'array', 'is_active' => 'boolean'];
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(StandardReference::class, 'standard_reference_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(StandardTableValue::class)->orderBy('sort_order');
    }
}
