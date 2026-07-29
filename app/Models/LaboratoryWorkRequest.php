<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaboratoryWorkRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'requested_date' => 'date',
        'contract_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
