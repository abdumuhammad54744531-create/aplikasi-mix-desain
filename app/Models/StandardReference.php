<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardReference extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['effective_at' => 'date', 'expires_at' => 'date', 'is_active' => 'boolean'];
    }

    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_id');
    }

    public function tables(): HasMany
    {
        return $this->hasMany(StandardTableHeader::class);
    }
}
