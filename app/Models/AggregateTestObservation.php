<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AggregateTestObservation extends Model
{
    protected $guarded = [];
    protected $casts = ['data' => 'array'];

    public function run()
    {
        return $this->belongsTo(AggregateTestRun::class, 'aggregate_test_run_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }
}
