<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ReportApproval extends Model {
 protected $guarded=[];
 protected $casts=['approved_at'=>'datetime','revoked_at'=>'datetime'];
 public function project(){return $this->belongsTo(Project::class)->withTrashed();}
 public function user(){return $this->belongsTo(User::class);}
}
