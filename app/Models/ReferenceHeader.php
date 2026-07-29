<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceHeader extends Model
{
    protected $guarded = [];
    protected $casts = ['effective_at' => 'date', 'is_active' => 'boolean'];
}
