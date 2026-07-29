<?php
namespace App\Http\Controllers;
use App\Models\{AggregateTestRun,CementTest,WaterTest,FineAggregateTest,CoarseAggregateTest,LaboratoryWorkflow,Project};
use App\Services\AuditService;
use App\Services\MixDesign\MixDesign2012Calculator;
use Illuminate\Http\Request;
class MixDesign2012Controller extends Controller {
 public function create(Request $r){return $this->showForm($r,false);}
 public function createCombined(Request $r){return $this->showForm($r,true);}
 private function showForm(Request $r,bool $combined){
  $project=$r->project?Project::find($r->project):null;
  $cement=$project?CementTest::where('project_id',$project->id)->latest()->first():null;$water=$project?WaterTest::where('project_id',$project->id)->latest()->first():null;
  $fine=$project?FineAggregateTest::where('project_id',$project->id)->latest()->first():null;$coarse=$project?CoarseAggregateTest::where('project_id',$project->id)->latest()->first():null;
  $required=['cement_sg'=>['Semen','Berat jenis semen',$cement?->specific_gravity],'fine_sg'=>['Pasir','Berat jenis SSD',$fine?->specific_gravity_ssd],
   'fine_absorption'=>['Pasir','Penyerapan',$fine?->absorption],'fine_moisture'=>['Pasir','Kadar air',$fine?->field_moisture],'fine_density'=>['Pasir','Berat isi padat',$fine?->compacted_bulk_density],
   'fine_fm'=>['Pasir','Modulus kehalusan',$fine?->fineness_modulus],'coarse_sg'=>['Kerikil','Berat jenis SSD',$coarse?->specific_gravity_ssd],
   'coarse_absorption'=>['Kerikil','Penyerapan',$coarse?->absorption],'coarse_moisture'=>['Kerikil','Kadar air',$coarse?->field_moisture],'coarse_density'=>['Kerikil','Berat isi padat',$coarse?->compacted_bulk_density]];
  $fineSieve=$project?AggregateTestRun::where('project_id',$project->id)->where('aggregate_type','fine')->where('test_type','sieve')->latest()->first():null;
  $coarseSieve=$project?AggregateTestRun::where('project_id',$project->id)->where('aggregate_type','coarse')->where('test_type','sieve')->latest()->first():null;
  $combinedLimits=$this->combinedGradationLimits();
  return view('mix-design-2012.form',compact('project','cement','water','fine','coarse','required','combined','fineSieve','coarseSieve','combinedLimits')+['projects'=>Project::where('status','aktif')->get()]);
 }
 public function store(Request $r,MixDesign2012Calculator $calc){return $this->saveDesign($r,$calc,false);}
 public function storeCombined(Request $r,MixDesign2012Calculator $calc){return $this->saveDesign($r,$calc,true);}
 private function saveDesign(Request $r,MixDesign2012Calculator $calc,bool $combined){
  $d=$r->validate(['project_id'=>'required|exists:projects,id','work_date'=>'required|date','data'=>'required|array','data.combined_fine_percent'=>($combined?'required':'nullable').'|numeric|between:0,100','data.gradation_max_size'=>($combined?'required':'nullable').'|in:10,20,40','data.gradation_curve'=>($combined?'required':'nullable').'|integer|between:1,5','notes'=>'nullable']);
  $cement=CementTest::where('project_id',$d['project_id'])->latest()->first();$water=WaterTest::where('project_id',$d['project_id'])->latest()->first();$fine=FineAggregateTest::where('project_id',$d['project_id'])->latest()->first();$coarse=CoarseAggregateTest::where('project_id',$d['project_id'])->latest()->first();
  $complete=$cement?->specific_gravity!==null&&$water&&$fine?->specific_gravity_ssd!==null&&$fine?->absorption!==null&&$fine?->field_moisture!==null&&$fine?->compacted_bulk_density!==null&&$fine?->fineness_modulus!==null&&$coarse?->specific_gravity_ssd!==null&&$coarse?->absorption!==null&&$coarse?->field_moisture!==null&&$coarse?->compacted_bulk_density!==null;
  if(!$complete)return back()->withInput()->withErrors(['data'=>'Desain campuran belum dapat disimpan karena hasil pengujian material belum lengkap.']);
  $optimized=null;if($combined){$fineSieve=AggregateTestRun::where('project_id',$d['project_id'])->where('aggregate_type','fine')->where('test_type','sieve')->latest()->first();$coarseSieve=AggregateTestRun::where('project_id',$d['project_id'])->where('aggregate_type','coarse')->where('test_type','sieve')->latest()->first();$optimized=$this->optimizeCombinedGradation($fineSieve,$coarseSieve,(int)$d['data']['gradation_max_size'],(int)$d['data']['gradation_curve']);if(!$optimized)return back()->withInput()->withErrors(['data.combined_fine_percent'=>'Analisis saringan pasir dan kerikil harus lengkap untuk menghitung gradasi gabungan.']);$d['data']['combined_mode']=1;$d['data']['combined_fine_percent']=$optimized['fine_percent'];$d['data']['combined_coarse_percent']=$optimized['coarse_percent'];$d['data']['combined_deviation']=$optimized['deviation'];}
  try{$result=$calc->calculate(array_map('floatval',$d['data']));if($combined)$result['combined_gradation_rows']=$optimized['rows'];}catch(\InvalidArgumentException $e){return back()->withInput()->withErrors(['data'=>$e->getMessage()]);}
  $type=$combined?'mix-design-2012-combined':'mix-design-2012';$record=LaboratoryWorkflow::create(['project_id'=>$d['project_id'],'type'=>$type,'number'=>($combined?'MD12G-':'MD12-').now()->format('ymdHis'),'work_date'=>$d['work_date'],'input_data'=>$d['data'],'result_data'=>$result,'notes'=>$d['notes']??null,'created_by'=>auth()->id()]);
  AuditService::record($combined?'Desain Campuran SNI 7656:2012 Gradasi Gabungan':'Desain Campuran SNI 7656:2012','simpan',$record);
  return redirect()->route('workflow.index',['type'=>'compressive-strength','project'=>$d['project_id']])->with('success','Desain campuran berhasil disimpan. Silakan lanjutkan pengujian kuat tekan.');
 }
 private function combinedGradationLimits():array{return [
  40=>['r375'=>[100,100,100,100],'r190'=>[50,59,67,75],'r095'=>[36,44,52,60],'r475'=>[24,32,40,47],'r236'=>[18,25,31,38],'r118'=>[12,17,24,30],'r060'=>[7,12,17,23],'r030'=>[3,7,11,15],'r015'=>[0,2,4,6]],
  20=>['r375'=>[null,null,null,null],'r190'=>[100,100,100,100],'r095'=>[45,55,65,75],'r475'=>[30,35,42,48],'r236'=>[23,28,35,42],'r118'=>[16,21,28,34],'r060'=>[9,14,21,27],'r030'=>[2,3,5,12],'r015'=>[0,0,2,4]],
  10=>['r375'=>[null,null,null,null],'r190'=>[null,null,null,null],'r095'=>[100,100,100,100],'r475'=>[30,45,60,75],'r236'=>[20,33,46,60],'r118'=>[16,26,37,46],'r060'=>[10,19,28,34],'r030'=>[4,8,14,20],'r015'=>[0,1,3,6]],
 ];}
 private function optimizeCombinedGradation(?AggregateTestRun $fineRun,?AggregateTestRun $coarseRun,int $maxSize,int $curve):?array{
  if(!$fineRun||!$coarseRun)return null;$fineCum=$fineRun->results['observations'][0]['sieve_cumulative']??[];$coarseCum=$coarseRun->results['observations'][0]['sieve_cumulative']??[];$limits=$this->combinedGradationLimits()[$maxSize]??null;if(!$limits)return null;
  $allKeys=array_keys($limits);$startKey=match($maxSize){10=>'r095',20=>'r190',40=>'r375'};$activeKeys=array_slice($allKeys,array_search($startKey,$allKeys,true));$fine=[];$coarse=[];$targets=[];foreach($limits as $key=>$curves){if(!in_array($key,$activeKeys,true))continue;$fine[$key]=in_array($key,['r375','r190','r095'])?100:(isset($fineCum[$key])?100-(float)$fineCum[$key]:null);$coarse[$key]=isset($coarseCum[$key])?100-(float)$coarseCum[$key]:(in_array($key,['r236','r118','r060','r030','r015'])?0:null);$available=array_values(array_filter($curves,fn($value)=>$value!==null));$targets[$key]=$curve===5?($available?array_sum($available)/count($available):null):($curves[$curve-1]??null);if($targets[$key]!==null&&($fine[$key]===null||$coarse[$key]===null))return null;}
  $bestFine=null;$bestDeviation=INF;for($tenths=0;$tenths<=1000;$tenths++){$finePercent=$tenths/10;$coarsePercent=100-$finePercent;$sum=0;$count=0;foreach($activeKeys as $key){$target=$targets[$key];if($target===null)continue;$combined=$fine[$key]*$finePercent/100+$coarse[$key]*$coarsePercent/100;$sum+=abs($combined-$target);$count++;}if($count){$average=$sum/$count;if($average<$bestDeviation-0.0000001){$bestDeviation=$average;$bestFine=$finePercent;}}}
  if($bestFine===null)return null;$bestCoarse=100-$bestFine;$rows=[];foreach($activeKeys as $key){$fineWeighted=$fine[$key]===null?null:$fine[$key]*$bestFine/100;$coarseWeighted=$coarse[$key]===null?null:$coarse[$key]*$bestCoarse/100;$combined=$fineWeighted===null||$coarseWeighted===null?null:$fineWeighted+$coarseWeighted;$target=$targets[$key];$rows[$key]=['fine'=>$fine[$key],'coarse'=>$coarse[$key],'fine_retained'=>$fine[$key]===null?null:100-$fine[$key],'coarse_retained'=>$coarse[$key]===null?null:100-$coarse[$key],'fine_weighted'=>$fineWeighted,'coarse_weighted'=>$coarseWeighted,'target'=>$target,'combined'=>$combined,'deviation'=>$combined===null||$target===null?null:abs($combined-$target)];}
  return ['fine_percent'=>round($bestFine,1),'coarse_percent'=>round($bestCoarse,1),'deviation'=>round($bestDeviation,4),'curve'=>$curve,'max_size'=>$maxSize,'active_sieves'=>$activeKeys,'rows'=>$rows];
 }
}
