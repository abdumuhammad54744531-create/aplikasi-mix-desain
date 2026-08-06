<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class AggregateTestRun extends Model {
    use SoftDeletes;
    protected $guarded=[]; protected $casts=['observations'=>'array','results'=>'array','tested_at'=>'date'];
    public function project(){return $this->belongsTo(Project::class)->withTrashed();}
    public function observationRecords(){return $this->hasMany(AggregateTestObservation::class)->orderBy('observation_number');}
    public function observationsForForm():array {
        $records=$this->observationRecords()->get();
        if($records->isNotEmpty())return $records->map(fn($record)=>['id'=>$record->id,'observation_number'=>$record->observation_number,...($record->data??[])])->all();
        return array_map(fn($data,$index)=>['id'=>null,'observation_number'=>$index+1,...$data],$this->observations??[],array_keys($this->observations??[]));
    }
}
