<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class MixDesign extends Model {
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['planned_at'=>'date','fc'=>'decimal:2','fcr'=>'decimal:2'];
    public function project(){ return $this->belongsTo(Project::class)->withTrashed(); }
}
