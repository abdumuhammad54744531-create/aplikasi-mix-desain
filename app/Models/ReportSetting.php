<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ReportSetting extends Model {
 public const FONT_FAMILIES=['Times New Roman','Arial','Calibri','Georgia','Garamond','Tahoma','Trebuchet MS','Verdana'];
 protected $guarded=[];
 protected $casts=['header_lines'=>'array','header_lines_enabled'=>'boolean'];
 public function resolvedHeaderLines(?LaboratoryProfile $laboratory=null):array{
  $defaults=[
   ['text'=>$laboratory?->name?:'LABORATORIUM BAHAN DAN STRUKTUR','size'=>22,'bold'=>true,'italic'=>false,'font'=>'Times New Roman','uppercase'=>true,'align'=>'center','margin_top'=>0,'margin_bottom'=>1,'line_height'=>1.1],
   ['text'=>$laboratory?->institution?:'PROGRAM STUDI TEKNIK SIPIL','size'=>16,'bold'=>true,'italic'=>false,'font'=>'Times New Roman','uppercase'=>true,'align'=>'center','margin_top'=>0,'margin_bottom'=>1,'line_height'=>1.1],
   ['text'=>'','size'=>12,'bold'=>false,'italic'=>false,'font'=>'Times New Roman','uppercase'=>false,'align'=>'center','margin_top'=>0,'margin_bottom'=>0,'line_height'=>1.1],
   ['text'=>'','size'=>11,'bold'=>false,'italic'=>false,'font'=>'Times New Roman','uppercase'=>false,'align'=>'center','margin_top'=>0,'margin_bottom'=>0,'line_height'=>1.1],
   ['text'=>'','size'=>10,'bold'=>false,'italic'=>false,'font'=>'Times New Roman','uppercase'=>false,'align'=>'center','margin_top'=>0,'margin_bottom'=>0,'line_height'=>1.1],
  ];
  $saved=$this->header_lines??[];
  return array_map(fn($default,$index)=>array_replace($default,is_array($saved[$index]??null)?$saved[$index]:[]),$defaults,array_keys($defaults));
 }
}
