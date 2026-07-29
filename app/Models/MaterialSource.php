<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class MaterialSource extends Model {
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['sampled_at'=>'date'];
    public function project(){ return $this->belongsTo(Project::class)->withTrashed(); }
}
