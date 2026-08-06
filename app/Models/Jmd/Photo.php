<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\SoftDeletes;

class Photo extends ProjectRecord
{
    use SoftDeletes;

    protected $table = 'jmd_photos';

    protected function casts(): array
    {
        return ['photographed_at' => 'date', 'crop_data' => 'array'];
    }
}
