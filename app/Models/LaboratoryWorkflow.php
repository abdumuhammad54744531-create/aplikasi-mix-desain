<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class LaboratoryWorkflow extends Model {
 use SoftDeletes;
 protected $guarded=[]; protected $casts=['input_data'=>'array','result_data'=>'array','work_date'=>'date'];
 public function project(){return $this->belongsTo(Project::class)->withTrashed();}
}
