<?php
namespace App\Http\Controllers;
use App\Models\{AggregateTestRun,CementTest,CoarseAggregateTest,FineAggregateTest,LaboratoryProfile,LaboratoryWorkflow,MaterialSource,Project,ReportApproval,ReportSetting,TestDocumentation,WaterTest};
use App\Services\AuditService;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
class WorkflowController extends Controller {
 public function index(Request $r,string $type){$c=$this->config($type);$projects=Project::where('status','aktif')->when($r->project,fn($q)=>$q->orWhere('id',$r->project))->get();$mixDesigns=[];
  if(in_array($type,['fresh-concrete','compressive-strength']))$mixDesigns=LaboratoryWorkflow::whereIn('type',['mix-design-2012','mix-design-2012-combined'])->latest()->get()->unique('project_id')->mapWithKeys(function($mix){$i=$mix->input_data;$o=$mix->result_data;return [$mix->project_id=>['number'=>$mix->number,'target_fc'=>$i['fc']??null,'target_slump'=>$i['slump_design']??null,'theoretical_density'=>$o['total_fresh_mass']??($i['fresh_density']??null),'batch_mass'=>($o['trial_cement']??0)+($o['trial_fine']??0)+($o['trial_coarse']??0)+($o['trial_water']??0),'design_volume'=>isset($i['trial_volume_liter'])?$i['trial_volume_liter']/1000:null]];});
  $records=LaboratoryWorkflow::with('project')->where('type',$type)->latest()->get();$savedRecords=$records->unique('project_id')->mapWithKeys(fn($record)=>[$record->project_id=>['id'=>$record->id,'number'=>$record->number,'work_date'=>$record->work_date?->format('Y-m-d'),'input_data'=>$record->input_data,'notes'=>$record->notes]]);
  return view('workflows.index',['type'=>$type,'config'=>$c,'selectedProject'=>$r->project,'projects'=>$projects,'mixDesigns'=>$mixDesigns,'records'=>$records,'savedRecords'=>$savedRecords]);}
 public function store(Request $r,string $type){$c=$this->config($type);
  if($type==='compressive-strength')return $this->storeCompressionBatch($r,$c);
  $rules=['workflow_id'=>'nullable|integer','project_id'=>'required|exists:projects,id','work_date'=>'required|date','notes'=>'nullable'];foreach($c['fields'] as $f)$rules['data.'.$f[0]]='required|numeric|min:0';$d=$r->validate($rules);
  try{$result=$this->calculate($type,array_map('floatval',$d['data']));}catch(InvalidArgumentException $e){return back()->withInput()->withErrors(['data'=>$e->getMessage()]);}
  $id=$d['workflow_id']??null;unset($d['workflow_id']);$attributes=['project_id'=>$d['project_id'],'type'=>$type,'work_date'=>$d['work_date'],'input_data'=>$d['data'],'result_data'=>$result,'notes'=>$d['notes']??null];
  $record=DB::transaction(function()use($id,$d,$type,$attributes){if($id){$record=LaboratoryWorkflow::whereKey($id)->where('project_id',$d['project_id'])->where('type',$type)->lockForUpdate()->firstOrFail();$record->update($attributes);return $record;}return LaboratoryWorkflow::create([...$attributes,'number'=>strtoupper(substr($type,0,3)).'-'.now()->format('ymdHis'),'created_by'=>auth()->id()]);});
  AuditService::record($c['title'],'hitung dan simpan',$record);return back()->with('success',$c['title'].' berhasil disimpan.');
 }
 private function storeCompressionBatch(Request $r,array $c){
  $d=$r->validate(['workflow_id'=>'nullable|integer','project_id'=>'required|exists:projects,id','work_date'=>'required|date','notes'=>'nullable','data.rows'=>'required|array|min:1',
   'data.rows.*.cast_date'=>'required|date','data.rows.*.test_date'=>'required|date|after_or_equal:data.rows.*.cast_date',
   'data.rows.*.diameter'=>'required|numeric|min:1','data.rows.*.height'=>'required|numeric|min:1','data.rows.*.weight'=>'required|numeric|min:0.001','data.rows.*.load_kn'=>'required|numeric|min:0.001']);
  $mix=LaboratoryWorkflow::where('project_id',$d['project_id'])->whereIn('type',['mix-design-2012','mix-design-2012-combined'])->latest()->first();
  if(!$mix||!isset($mix->input_data['fc']))return back()->withInput()->withErrors(['data.target_fc'=>'Mutu sasaran belum tersedia karena proyek belum memiliki desain campuran.']);
  $target=(float)$mix->input_data['fc'];$details=[];$estimated=[];
  foreach($d['data']['rows'] as $i=>$row){$age=\Carbon\Carbon::parse($row['cast_date'])->diffInDays(\Carbon\Carbon::parse($row['test_date']));$factor=$this->ageFactor($age);$area=pi()*(float)$row['diameter']**2/4;$actual=(float)$row['load_kn']*1000/$area;$estimate=$actual/$factor;$estimated[]=$estimate;$details[]=[...$row,'number'=>$i+1,'age_days'=>$age,'area_mm2'=>$area,'actual_mpa'=>$actual,'age_factor'=>$factor,'estimated_28_mpa'=>$estimate,'estimated_k_kgcm2'=>$estimate*10.19716213];}
  $count=count($estimated);$mean=array_sum($estimated)/$count;$sd=0;if($count>1){foreach($estimated as $x)$sd+=($x-$mean)**2;$sd=sqrt($sd/($count-1));}$characteristic=$mean-1.64*$sd;
  $result=['Jumlah benda uji'=>$count,'Sasaran f\'c (MPa)'=>$target,'Rata-rata perkiraan 28 hari (MPa)'=>$mean,'Standar deviasi sampel (MPa)'=>$sd,'Kuat tekan karakteristik (MPa)'=>$characteristic,'Mutu karakteristik (kg/cm²)'=>$characteristic*10.19716213,'Status'=>$characteristic>=$target?'Memenuhi':'Tidak memenuhi','detail_rows'=>$details];
  $id=$d['workflow_id']??null;$attributes=['project_id'=>$d['project_id'],'type'=>'compressive-strength','work_date'=>$d['work_date'],'input_data'=>['target_fc'=>$target,'mix_design_number'=>$mix->number,'rows'=>$d['data']['rows']],'result_data'=>$result,'notes'=>$d['notes']??null];
  $record=DB::transaction(function()use($id,$d,$attributes){if($id){$record=LaboratoryWorkflow::whereKey($id)->where('project_id',$d['project_id'])->where('type','compressive-strength')->lockForUpdate()->firstOrFail();$record->update($attributes);return $record;}return LaboratoryWorkflow::create([...$attributes,'number'=>'COM-'.now()->format('ymdHis'),'created_by'=>auth()->id()]);});
  AuditService::record($c['title'],'hitung paket benda uji dan simpan',$record);return back()->with('success','Pengujian kuat tekan seluruh benda uji berhasil dihitung dan disimpan.');
 }
 private function ageFactor(int $age):float{$ages=[3,7,14,21,28,90,365];$factors=[.40,.65,.88,.95,1,1.20,1.35];if($age<=$ages[0])return $factors[0];if($age>=$ages[count($ages)-1])return $factors[count($factors)-1];for($i=0;$i<count($ages)-1;$i++)if($age>=$ages[$i]&&$age<=$ages[$i+1])return $factors[$i]+($age-$ages[$i])/($ages[$i+1]-$ages[$i])*($factors[$i+1]-$factors[$i]);return 1;}
 public function reports(){
  $projects=Project::orderByDesc('updated_at')->get()->map(function($project){$records=$this->projectReportRecords($project);$project->report_count=$records->count();$statuses=$records->pluck('status');$project->report_status=$statuses->contains('draft')?'draft':($statuses->contains('diperiksa')?'diperiksa':($statuses->isNotEmpty()?'disetujui':'belum ada'));return $project;});
  return view('workflows.reports',compact('projects'));
 }
 public function reportProject(Project $project){$records=$this->projectReportRecords($project);$statuses=$records->pluck('status');$reportStatus=$project->document_status==='ditolak'?'ditolak':($statuses->contains('draft')?'draft':($statuses->contains('diperiksa')?'diperiksa':($statuses->isNotEmpty()?'disetujui':'belum ada')));$approvals=$project->reportApprovals()->with('user')->latest('approved_at')->get();return view('workflows.report-project',compact('project','records','reportStatus','approvals'));}
 public function finalReport(Project $project){$records=$this->projectReportRecords($project);abort_unless($records->isNotEmpty()&&!$records->pluck('status')->contains(fn($status)=>$status!=='disetujui'),403,'Laporan akhir hanya tersedia setelah seluruh berkas disetujui.');return $this->renderFinalReport($project,false);}
 public function publicVerification(string $code){$project=Project::withTrashed()->where('verification_code',$code)->with(['reportApprovals'=>fn($q)=>$q->with('user')->orderBy('approved_at')])->firstOrFail();$currentHash=$this->documentHash($project);$hashValid=(bool)($project->document_hash&&hash_equals($project->document_hash,$currentHash));$explicit=in_array($project->document_status,['direvisi','dicabut','dibatalkan','ditolak'],true);$verificationStatus=$explicit?$project->document_status:($hashValid?$project->document_status:'dokumen berubah');return view('public.verification',compact('project','hashValid','verificationStatus'));}
 public function publicSignerVerification(string $code,string $signature){
  $project=Project::withTrashed()->where('verification_code',$code)->whereNotNull('legalized_at')->firstOrFail();$setting=ReportSetting::firstOrCreate([]);
  $expected=$this->signerCode($project,$setting);abort_unless(hash_equals($expected,strtoupper($signature)),404);
  return view('public.signer-verification',compact('project','setting','expected'));
 }
 public function publicApprovalVerification(string $token){
  $approval=ReportApproval::with(['project','user'])->where('verification_token',$token)->firstOrFail();$project=$approval->project;$currentHash=$this->documentHash($project);
  $hashValid=hash_equals($approval->document_hash,$currentHash);$latestRevision=$project->report_revision;$effectiveStatus=$approval->status;
  if($approval->revision<$latestRevision&&$effectiveStatus==='valid')$effectiveStatus='direvisi';elseif(!$hashValid&&$effectiveStatus==='valid')$effectiveStatus='dokumen berubah';
  return view('public.approval-verification',compact('approval','project','hashValid','effectiveStatus'));
 }
 public function publicReport(string $code){$project=Project::withTrashed()->where('verification_code',$code)->whereNotNull('legalized_at')->firstOrFail();return $this->renderFinalReport($project,true);}
 public function publicDownload(string $code){
  $project=Project::withTrashed()->where('verification_code',$code)->whereNotNull('legalized_at')->firstOrFail();
  $options=new \Dompdf\Options();$options->set('isRemoteEnabled',true);$options->set('chroot',public_path());$options->set('defaultMediaType','print');
  $pdf=new \Dompdf\Dompdf($options);$pdf->loadHtml($this->renderFinalReport($project,true)->render(),'UTF-8');$pdf->setPaper('A4');$pdf->render();
  $filename='Laporan-'.preg_replace('/[^A-Za-z0-9_-]+/','-',$project->number).'.pdf';
  return response($pdf->output(),200,['Content-Type'=>'application/pdf','Content-Disposition'=>'attachment; filename="'.$filename.'"']);
 }
 private function renderFinalReport(Project $project,bool $isPublic){
  $materialTests=collect([
   'Pemeriksaan Semen'=>CementTest::where('project_id',$project->id)->latest()->get(),
   'Pemeriksaan Air'=>WaterTest::where('project_id',$project->id)->latest()->get(),
   'Pemeriksaan Pasir'=>FineAggregateTest::where('project_id',$project->id)->latest()->get(),
   'Pemeriksaan Kerikil'=>CoarseAggregateTest::where('project_id',$project->id)->latest()->get(),
  ]);
  $aggregateRuns=AggregateTestRun::where('project_id',$project->id)->orderBy('aggregate_type')->orderBy('tested_at')->get();
  $reportMixTypes=$project->includedMixDesignTypes();
  $mixDesigns=LaboratoryWorkflow::where('project_id',$project->id)->whereIn('type',$reportMixTypes)->orderBy('work_date')->get();
  $strengthTests=LaboratoryWorkflow::where('project_id',$project->id)->where('type','compressive-strength')->orderBy('work_date')->get();
  $materialSources=MaterialSource::where('project_id',$project->id)->orderBy('type')->orderBy('name')->get();
  $documents=TestDocumentation::where('project_id',$project->id)->orderBy('module')->orderBy('sort_order')->get()->groupBy('module');
  $laboratory=LaboratoryProfile::first();$setting=ReportSetting::firstOrCreate([]);
  $reportUrl=$project->verification_code?route('public.verify',$project->verification_code):null;$qrDataUri=$reportUrl?(new PngWriter())->write(new QrCode(data:$reportUrl,size:220,margin:8))->getDataUri():null;
  $approvalQrCodes=$project->reportApprovals()->with('user')->where('revision',$project->report_revision)->where('status','valid')->orderBy('approved_at')->get()->map(function($approval){$approval->qr_data_uri=(new PngWriter())->write(new QrCode(data:route('public.approval',$approval->verification_token),size:220,margin:8))->getDataUri();return $approval;});
  return view('workflows.final-report',compact('project','materialTests','materialSources','aggregateRuns','mixDesigns','reportMixTypes','strengthTests','documents','laboratory','setting','qrDataUri','approvalQrCodes','isPublic'));
 }
 private function signerCode(Project $project,ReportSetting $setting):string{return strtoupper(substr(hash_hmac('sha256',$project->verification_code.'|'.($setting->signer_name?:'Kepala Laboratorium').'|'.($setting->signer_position?:'Kepala Laboratorium'),config('app.key')),0,20));}
 public function updateReportStatus(Request $r,Project $project){
  $d=$r->validate(['status'=>'required|in:diperiksa,disetujui,ditolak,dikembalikan,revisi','approval_role'=>'nullable|in:pemeriksa,mengetahui,menyetujui','notes'=>'nullable|max:1000']);$status=$d['status'];
  if($status==='revisi'){abort_unless($project->locked_at,422,'Dokumen belum dikunci.');$project->reportApprovals()->where('status','valid')->update(['status'=>'direvisi','revoked_at'=>now()]);$project->increment('report_revision');$project->update(['document_status'=>'direvisi','locked_at'=>null,'status'=>'aktif','legalized_at'=>null]);$this->setRecordStatus($project,'draft');return back()->with('success','Revisi baru dibuat. Persetujuan lama tetap dapat dipindai dengan status DIREVISI.');}
  if(in_array($status,['ditolak','dikembalikan'])){if($status==='ditolak')$project->reportApprovals()->where('status','valid')->update(['status'=>'ditolak','revoked_at'=>now()]);$project->update(['document_status'=>$status,'locked_at'=>null]);$this->setRecordStatus($project,'draft');AuditService::record('Laporan Proyek',$status,$project);return back()->with('success',$status==='ditolak'?'Laporan ditolak.':'Laporan dikembalikan untuk diperbaiki.');}
  if($status==='diperiksa'){$this->setRecordStatus($project,'diperiksa');$project->update(['document_status'=>'diperiksa']);AuditService::record('Laporan Proyek','ajukan pemeriksaan',$project);return back()->with('success','Laporan diajukan kepada pejabat yang berwenang.');}
  $hash=$this->documentHash($project);$approval=ReportApproval::create(['approval_id'=>Str::uuid(),'verification_token'=>Str::random(64),'project_id'=>$project->id,'user_id'=>auth()->id(),'revision'=>$project->report_revision,'approval_role'=>$d['approval_role']??'menyetujui','approval_type'=>'pengesahan laporan','status'=>'valid','document_hash'=>$hash,'ip_address'=>$r->ip(),'user_agent'=>Str::limit((string)$r->userAgent(),1000,''),'approved_at'=>now(),'notes'=>$d['notes']??null]);
  $this->setRecordStatus($project,'disetujui');$project->update(['status'=>'selesai','document_status'=>'valid','document_hash'=>$hash,'locked_at'=>now(),'updated_by'=>auth()->id(),'verification_code'=>$project->verification_code?:Str::uuid(),'legalized_at'=>now(),'legalized_by'=>auth()->id()]);AuditService::record('Laporan Proyek','setujui '.$approval->approval_role,$project);return back()->with('success','Persetujuan tersimpan, dokumen dikunci, dan QR pejabat dibuat dengan token unik.');
 }
 private function setRecordStatus(Project $project,string $status):void{
  foreach([AggregateTestRun::class,CementTest::class,WaterTest::class,FineAggregateTest::class,CoarseAggregateTest::class] as $model)$model::where('project_id',$project->id)->update(['status'=>$status]);
  $includedMixTypes=$project->includedMixDesignTypes();
  LaboratoryWorkflow::where('project_id',$project->id)->where(function($query)use($includedMixTypes){
   $query->whereNotIn('type',['mix-design-2012','mix-design-2012-combined'])->orWhereIn('type',$includedMixTypes);
  })->update(['status'=>$status]);
 }
 private function documentHash(Project $project):string{$records=$this->projectReportRecords($project)->map(fn($r)=>[$r->module,$r->number,$r->date?->format('c'),$r->result])->values()->all();return hash('sha256',json_encode(['project'=>$project->only(['number','name','owner','location','concrete_grade','construction_type','report_include_mix_design_2012','report_include_mix_design_2012_combined']),'revision'=>$project->report_revision,'records'=>$records],JSON_UNESCAPED_UNICODE|JSON_PRESERVE_ZERO_FRACTION));}
 private function projectReportRecords(Project $project){$items=collect();foreach([
   ['Pemeriksaan Semen',CementTest::class],['Pemeriksaan Air',WaterTest::class],['Pemeriksaan Pasir',FineAggregateTest::class],['Pemeriksaan Kerikil',CoarseAggregateTest::class],
  ] as [$module,$model])foreach($model::where('project_id',$project->id)->latest()->get() as $record)$items->push((object)['module'=>$module,'number'=>$record->test_number,'date'=>$record->tested_at,'status'=>$record->status,'result'=>'Data karakteristik material']);
  foreach(AggregateTestRun::where('project_id',$project->id)->latest()->get() as $record)$items->push((object)['module'=>ucwords(str_replace('-',' ',$record->test_type)).' '.($record->aggregate_type==='fine'?'Pasir':'Kerikil'),'number'=>$record->test_number,'date'=>$record->tested_at,'status'=>$record->status,'result'=>collect($record->results['averages']??[])->map(fn($v,$k)=>ucwords(str_replace('_',' ',$k)).': '.number_format($v,3,',','.'))->join('; ')]);
  $includedMixTypes=$project->includedMixDesignTypes();
  foreach(LaboratoryWorkflow::where('project_id',$project->id)->latest()->get() as $record){
   if(in_array($record->type,['mix-design-2012','mix-design-2012-combined'],true)&&!in_array($record->type,$includedMixTypes,true))continue;
   $items->push((object)['module'=>match($record->type){'mix-design-2012'=>'Desain Campuran SNI 7656:2012','mix-design-2012-combined'=>'Desain Campuran SNI 7656:2012 (Gradasi Gabungan)','compressive-strength'=>'Kuat Tekan',default=>ucwords(str_replace('-',' ',$record->type))},'number'=>$record->number,'date'=>$record->work_date,'status'=>$record->status,'result'=>collect($record->result_data)->reject(fn($v)=>is_array($v))->take(3)->map(fn($v,$k)=>$k.': '.(is_numeric($v)?number_format($v,3,',','.'):$v))->join('; ')]);
  }
  return $items->sortByDesc(fn($x)=>$x->date?->format('Y-m-d'))->values();
 }
 private function calculate(string $t,array $v):array{return match($t){
  'combined-aggregate'=>$this->combined($v),
  'moisture-correction'=>$this->moisture($v),
  'trial-mix'=>$this->trial($v),
  'fresh-concrete'=>$this->fresh($v),
  'specimen'=>$this->specimen($v),
  'compressive-strength'=>$this->compression($v),
  'evaluation'=>$this->evaluation($v),
  default=>throw new InvalidArgumentException('Modul tidak dikenali.')};}
 private function combined($v){$total=$v['fine_percent']+$v['coarse_percent'];return ['Total agregat (%)'=>$total,'Agregat halus (kg)'=>$v['total_mass']*$v['fine_percent']/100,'Agregat kasar (kg)'=>$v['total_mass']*$v['coarse_percent']/100,'Status'=>abs($total-100)<.001?'Memenuhi 100%':'Tidak memenuhi 100%'];}
 private function moisture($v){$fd=$v['fine_ssd']/(1+$v['fine_absorption']/100);$cd=$v['coarse_ssd']/(1+$v['coarse_absorption']/100);$fw=$fd*(1+$v['fine_moisture']/100);$cw=$cd*(1+$v['coarse_moisture']/100);$free=$fd*($v['fine_moisture']-$v['fine_absorption'])/100+$cd*($v['coarse_moisture']-$v['coarse_absorption'])/100;return ['Pasir lapangan (kg)'=>$fw,'Kerikil lapangan (kg)'=>$cw,'Air bebas agregat (kg)'=>$free,'Air ditambahkan (kg)'=>max(0,$v['design_water']-$free)];}
 private function trial($v){$factor=$v['trial_volume']/1000*(1+$v['waste']/100);return ['Semen (kg)'=>$v['cement_m3']*$factor,'Air (kg)'=>$v['water_m3']*$factor,'Pasir (kg)'=>$v['fine_m3']*$factor,'Kerikil (kg)'=>$v['coarse_m3']*$factor,'Faktor volume'=>$factor];}
 private function fresh($v){if($v['actual_density']<=0||$v['design_volume']<=0)throw new InvalidArgumentException('Berat isi aktual dan volume rencana harus lebih dari nol.');$actualVolume=$v['batch_mass']/$v['actual_density'];return ['Selisih slump (mm)'=>$v['actual_slump']-$v['target_slump'],'Volume aktual (mÂ³)'=>$actualVolume,'Hasil volume'=>$actualVolume/$v['design_volume'],'Selisih berat isi (kg/mÂ³)'=>$v['actual_density']-$v['theoretical_density']];}
 private function specimen($v){$volume=pi()*($v['diameter']/1000)**2/4*($v['height']/1000);return ['Luas tekan (mmÂ²)'=>pi()*$v['diameter']**2/4,'Volume benda uji (mÂ³)'=>$volume,'Berat isi benda uji (kg/mÂ³)'=>$volume>0?$v['weight']/$volume:0,'Umur rencana (hari)'=>$v['test_age']];}
 private function compression($v){$area=pi()*$v['diameter']**2/4;if($area<=0)throw new InvalidArgumentException('Diameter harus lebih dari nol.');$fc=$v['load_kn']*1000/$area;return ['Luas tekan (mmÂ²)'=>$area,'Kuat tekan (MPa)'=>$fc,'Pencapaian (%)'=>$v['target_fc']>0?$fc/$v['target_fc']*100:0,'Status'=>$fc>=$v['target_fc']?'Memenuhi':'Tidak memenuhi'];}
 private function evaluation($v){if($v['target_fc']<=0)throw new InvalidArgumentException('Kuat tekan sasaran harus lebih dari nol.');$strength=$v['actual_fc']/$v['target_fc']*100;$slumpOk=$v['actual_slump']>=$v['slump_min']&&$v['actual_slump']<=$v['slump_max'];return ['Pencapaian kuat tekan (%)'=>$strength,'Status slump'=>$slumpOk?'Sesuai':'Tidak sesuai','Kesimpulan'=>$strength>=100&&$slumpOk?'Campuran diterima':($strength>=90?'Perlu penyesuaian':'Campuran ditolak')];}
 private function config(string $t):array {$all=[
 'combined-aggregate'=>['title'=>'Gabungan Agregat','fields'=>[['fine_percent','Agregat halus','%'],['coarse_percent','Agregat kasar','%'],['total_mass','Total massa campuran','kg']]],
 'moisture-correction'=>['title'=>'Koreksi Kadar Air','fields'=>[['fine_ssd','Pasir SSD','kg'],['fine_absorption','Penyerapan pasir','%'],['fine_moisture','Kadar air pasir','%'],['coarse_ssd','Kerikil SSD','kg'],['coarse_absorption','Penyerapan kerikil','%'],['coarse_moisture','Kadar air kerikil','%'],['design_water','Air rencana','kg']]],
 'trial-mix'=>['title'=>'Campuran Percobaan','fields'=>[['trial_volume','Volume percobaan','liter'],['waste','Faktor kehilangan','%'],['cement_m3','Semen per mÂ³','kg'],['water_m3','Air per mÂ³','kg'],['fine_m3','Pasir per mÂ³','kg'],['coarse_m3','Kerikil per mÂ³','kg']]],
 'fresh-concrete'=>['title'=>'Pemeriksaan Beton Segar','fields'=>[['target_slump','Slump sasaran','mm'],['actual_slump','Slump aktual','mm'],['theoretical_density','Berat isi teoritis','kg/mÂ³'],['actual_density','Berat isi aktual','kg/mÂ³'],['batch_mass','Massa adukan','kg'],['design_volume','Volume rencana','mÂ³']]],
 'specimen'=>['title'=>'Pembuatan Benda Uji','fields'=>[['diameter','Diameter silinder','mm'],['height','Tinggi silinder','mm'],['weight','Berat benda uji','kg'],['test_age','Umur pengujian','hari']]],
 'compressive-strength'=>['title'=>'Pengujian Kuat Tekan','fields'=>[['diameter','Diameter aktual','mm'],['load_kn','Beban maksimum','kN'],['target_fc','Kuat tekan sasaran','MPa']]],
 'evaluation'=>['title'=>'Evaluasi Desain Campuran','fields'=>[['target_fc','Kuat tekan sasaran','MPa'],['actual_fc','Kuat tekan aktual','MPa'],['slump_min','Slump minimum','mm'],['slump_max','Slump maksimum','mm'],['actual_slump','Slump aktual','mm']]]];abort_unless(isset($all[$t]),404);return $all[$t];}
}

