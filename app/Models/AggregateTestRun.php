<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class AggregateTestRun extends Model {
    use SoftDeletes;
    protected $guarded=[]; protected $casts=['observations'=>'array','results'=>'array','tested_at'=>'date'];
    public function project(){return $this->belongsTo(Project::class)->withTrashed();}
}
