<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StandardTableValue extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['dimension_values' => 'array', 'is_active' => 'boolean'];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(StandardTableHeader::class, 'standard_table_header_id');
    }
}
