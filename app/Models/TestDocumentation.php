<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestDocumentation extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['documented_at' => 'date'];

    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }
}
