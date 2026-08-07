<?php
namespace App\Http\Controllers;
use App\Models\{AggregateTestObservation,AggregateTestRun,MaterialSource,Project};
use App\Services\{AggregateTestCalculator,AggregateTestSummaryService,AuditService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AggregateTestController extends Controller {
    public function worksheet(string $aggregate){
        abort_unless(in_array($aggregate,['fine','coarse']),404);
        $savedRuns=collect();$latestRuns=AggregateTestRun::where('aggregate_type',$aggregate)->latest()->get();
        foreach($latestRuns as $run){$payload=[
                'number'=>$run->test_number,'sample_number'=>$run->sample_number,'tested_at'=>$run->tested_at->format('Y-m-d'),
                'id'=>$run->id,'technician'=>$run->technician,'notes'=>$run->notes,'observations'=>$run->observationsForForm(),'results'=>$run->results,
            ];$exactKey=$run->project_id.'-'.($run->material_source_id??0).'-'.$run->test_type;$fallbackKey=$run->project_id.'-any-'.$run->test_type;if(!$savedRuns->has($exactKey))$savedRuns->put($exactKey,$payload);if(!$savedRuns->has($fallbackKey))$savedRuns->put($fallbackKey,$payload);}
        return view('aggregate-tests.worksheet',['aggregate'=>$aggregate,'tests'=>$this->tests($aggregate),
            'projects'=>Project::where('status','aktif')->get(),
            'materials'=>MaterialSource::whereIn('type',$aggregate==='fine'?['Pasir']:['Kerikil','Batu pecah'])->get(),
            'savedRuns'=>$savedRuns]);
    }
    public function storeWorksheet(Request $r,string $aggregate,AggregateTestCalculator $calculator,AggregateTestSummaryService $summaryService){
        abort_unless(in_array($aggregate,['fine','coarse']),404); $tests=$this->tests($aggregate);
        $data=$r->validate(['project_id'=>'required|exists:projects,id','material_source_id'=>'nullable|exists:material_sources,id',
            'sample_number'=>'required','tested_at'=>'required|date','technician'=>'required','runs'=>'required|array','notes'=>'nullable',
            'runs.*.observations'=>'required|array|min:1','runs.*.observations.*.id'=>'nullable|integer',
            'runs.*.observations.*.*'=>'nullable',
            'source_name'=>'nullable|max:255','source_quarry'=>'nullable|max:255','source_supplier'=>'nullable|max:255','source_sample_number'=>'nullable|max:255','source_condition'=>'nullable|max:255']);
        $sourceData=array_filter(['name'=>$data['source_name']??null,'quarry'=>$data['source_quarry']??null,'supplier'=>$data['source_supplier']??null,'sample_number'=>$data['source_sample_number']??null,'condition'=>$data['source_condition']??null],fn($value)=>$value!==null);
        unset($data['source_name'],$data['source_quarry'],$data['source_supplier'],$data['source_sample_number'],$data['source_condition']);
        $sourceAudit=null;
        try{$created=DB::transaction(function()use($data,$sourceData,$tests,$aggregate,$calculator,$summaryService,&$sourceAudit){
            if(!empty($data['material_source_id'])){$source=MaterialSource::findOrFail($data['material_source_id']);abort_unless($source->project_id===null||(int)$source->project_id===(int)$data['project_id'],422);$before=$source->toArray();$source->update([...$sourceData,'updated_by'=>auth()->id()]);$sourceAudit=[$source,$before];}
            $items=[];foreach($tests as $type=>$config){$obs=$data['runs'][$type]['observations']??[];$items[]=$this->persistRun($data,$aggregate,$type,$obs,$calculator,count($items));}
            $summaryService->sync((int)$data['project_id'],isset($data['material_source_id'])?(int)$data['material_source_id']:null,$aggregate);
            return $items;
        });}catch(\InvalidArgumentException $e){return back()->withInput()->withErrors(['runs'=>$e->getMessage()]);}
        if($sourceAudit)AuditService::record('Sumber Material','ubah dari pemeriksaan agregat',$sourceAudit[0],$sourceAudit[1]);
        foreach($created as $run)AuditService::record('Paket Pengujian Agregat','hitung dan simpan',$run);
        return redirect()->route('material-results.project',$data['project_id'])->with('success','Seluruh hasil pengujian berhasil dihitung dan disimpan.');
    }
    public function menu(string $aggregate){
        abort_unless(in_array($aggregate,['fine','coarse']),404);
        $tests=$this->tests($aggregate); $runs=AggregateTestRun::where('aggregate_type',$aggregate)->latest()->limit(10)->get();
        return view('aggregate-tests.menu',compact('aggregate','tests','runs'));
    }
    public function create(string $aggregate,string $test){
        $tests=$this->tests($aggregate); abort_unless(isset($tests[$test]),404);
        $savedRuns=collect();
        foreach(AggregateTestRun::where('aggregate_type',$aggregate)->where('test_type',$test)->latest()->get() as $run){
            $payload=['number'=>$run->test_number,'sample_number'=>$run->sample_number,'tested_at'=>$run->tested_at->format('Y-m-d'),
                'id'=>$run->id,'technician'=>$run->technician,'notes'=>$run->notes,'observations'=>$run->observationsForForm()];
            $exact=$run->project_id.'-'.($run->material_source_id??0);$fallback=$run->project_id.'-any';
            if(!$savedRuns->has($exact))$savedRuns->put($exact,$payload);if(!$savedRuns->has($fallback))$savedRuns->put($fallback,$payload);
        }
        return view('aggregate-tests.form',['aggregate'=>$aggregate,'test'=>$test,'config'=>$tests[$test],
            'projects'=>Project::where('status','aktif')->get(),
            'materials'=>MaterialSource::whereIn('type',$aggregate==='fine'?['Pasir']:['Kerikil','Batu pecah'])->get(),
            'savedRuns'=>$savedRuns]);
    }
    public function store(Request $r,string $aggregate,string $test,AggregateTestCalculator $calculator,AggregateTestSummaryService $summaryService){
        $tests=$this->tests($aggregate); abort_unless(isset($tests[$test]),404);
        $data=$r->validate(['project_id'=>'required|exists:projects,id','material_source_id'=>'nullable|exists:material_sources,id',
            'sample_number'=>'required','tested_at'=>'required|date','technician'=>'required','observations'=>'required|array|min:1','observations.*'=>'array',
            'observations.*.id'=>'nullable|integer','observations.*.*'=>'nullable','notes'=>'nullable']);
        try{$run=DB::transaction(function()use($data,$aggregate,$test,$calculator,$summaryService){$run=$this->persistRun($data,$aggregate,$test,$data['observations'],$calculator);$summaryService->sync((int)$data['project_id'],isset($data['material_source_id'])?(int)$data['material_source_id']:null,$aggregate);return $run;});}catch(\InvalidArgumentException $e){return back()->withInput()->withErrors(['observations'=>$e->getMessage()]);}
        AuditService::record('Pengujian '.$tests[$test]['label'],'hitung dan simpan',$run);
        return view('aggregate-tests.result',['run'=>$run,'config'=>$tests[$test]]);
    }
    public function destroyObservation(Project $project,AggregateTestRun $run,AggregateTestObservation $observation,AggregateTestCalculator $calculator,AggregateTestSummaryService $summaryService){
        abort_unless($run->project_id===$project->id&&$observation->project_id===$project->id&&$observation->aggregate_test_run_id===$run->id,404);
        if($run->observationRecords()->count()<=1)return response()->json(['message'=>'Minimal satu observasi harus tetap tersedia.'],422);
        DB::transaction(function()use($run,$observation,$calculator,$summaryService){
            $observation->delete();
            $records=$run->observationRecords()->get();
            foreach($records as $index=>$record)$record->update(['observation_number'=>$index+1]);
            $plain=$records->map(fn($record)=>$record->data)->all();
            $run->update(['observations'=>$plain,'results'=>$calculator->calculate($run->aggregate_type,$run->test_type,$plain)]);
            $summaryService->sync($run->project_id,$run->material_source_id,$run->aggregate_type);
        });
        AuditService::record('Observasi Pengujian Agregat','hapus',$run);
        return response()->json(['message'=>'Observasi berhasil dihapus.']);
    }
    private function persistRun(array $data,string $aggregate,string $test,array $observations,AggregateTestCalculator $calculator,int $suffix=0):AggregateTestRun {
        $project=Project::whereKey($data['project_id'])->where('status','aktif')->firstOrFail();
        $sourceId=$data['material_source_id']??null;
        $query=AggregateTestRun::where('project_id',$project->id)->where('aggregate_type',$aggregate)->where('test_type',$test);
        $sourceId?$query->where('material_source_id',$sourceId):$query->whereNull('material_source_id');
        $run=$query->latest()->lockForUpdate()->first();
        $attributes=['project_id'=>$project->id,'material_source_id'=>$sourceId,'sample_number'=>$data['sample_number'],
            'tested_at'=>$data['tested_at'],'technician'=>$data['technician'],'notes'=>$data['notes']??null,
            'aggregate_type'=>$aggregate,'test_type'=>$test,'created_by'=>auth()->id()];
        if(!$run){$attributes['test_number']=strtoupper(substr($aggregate,0,1).substr($test,0,3)).'-'.now()->format('ymdHisv').'-'.$suffix;$attributes['observations']=[];$attributes['results']=[];$run=AggregateTestRun::create($attributes);}
        else $run->update($attributes);
        foreach(array_values($observations) as $index=>$item){
            $id=$item['id']??null;unset($item['id'],$item['observation_number']);
            if($id){$record=AggregateTestObservation::whereKey($id)->where('aggregate_test_run_id',$run->id)->where('project_id',$project->id)->firstOrFail();$record->update(['observation_number'=>$index+1,'data'=>$item]);}
            elseif($record=$run->observationRecords()->where('observation_number',$index+1)->first())$record->update(['data'=>$item]);
            else $run->observationRecords()->create(['project_id'=>$project->id,'observation_number'=>$index+1,'data'=>$item]);
        }
        $records=$run->observationRecords()->get();$plain=$records->map(fn($record)=>$record->data)->all();
        $result=$calculator->calculate($aggregate,$test,$plain);$run->update(['observations'=>$plain,'results'=>$result]);
        return $run->fresh();
    }
    private function tests(string $a):array {
        $base=[
        'moisture'=>['label'=>'Kadar Air','standard'=>'SNI 1971:2011','icon'=>'droplet-half',
            'fields'=>[['container','Massa wadah','g'],['wet_container','Massa wadah + benda uji sebelum oven','g'],['dry_container','Massa wadah + benda uji setelah oven','g']],
            'process'=>[['wet_sample','D','Benda uji sebelum oven','B − A','g'],['dry_sample','E','Benda uji setelah oven','C − A','g'],['moisture','KA','Kadar air','(D − E) / E × 100','%']]],
        'silt'=>['label'=>'Kadar Lumpur','standard'=>'Metode pencucian','icon'=>'water',
            'fields'=>[['dry_before','Massa kering sebelum pencucian','g'],['dry_after','Massa kering setelah pencucian','g']],
            'process'=>[['mass_lost','C','Massa lumpur yang hilang','A − B','g'],['silt','KL','Kadar lumpur','C / A × 100','%']]],
        'specific-gravity'=>['label'=>'Berat Jenis dan Penyerapan','standard'=>$a==='fine'?'SNI 1970:2016':'SNI 1969:2016','icon'=>'speedometer',
            'fields'=>$a==='fine'?
                [['oven_dry','Massa benda uji kering oven','g'],['pyc_water','Massa piknometer + air','g'],['pyc_sample_water','Massa piknometer + benda uji + air','g'],['ssd','Massa benda uji SSD','g']]:
                [['oven_dry','Massa benda uji kering oven','g'],['ssd','Massa benda uji SSD di udara','g'],['submerged','Massa benda uji dalam air','g']],
            'process'=>$a==='fine'?
                [['displaced','E','Massa air yang dipindahkan','B + D − C','g'],['bulk_dry','BJ Kering','Berat jenis curah kering','A / E',''],['bulk_ssd','BJ SSD','Berat jenis curah SSD','D / E',''],['apparent','BJ Semu','Berat jenis semu','A / (B + A − C)',''],['absorption','P','Penyerapan','(D − A) / A × 100','%']]:
                [['displaced','D','Massa air yang dipindahkan','B − C','g'],['bulk_dry','BJ Kering','Berat jenis curah kering','A / D',''],['bulk_ssd','BJ SSD','Berat jenis curah SSD','B / D',''],['apparent','BJ Semu','Berat jenis semu','A / (A − C)',''],['absorption','P','Penyerapan','(B − A) / A × 100','%']]],
        'bulk-density'=>['label'=>'Berat Isi, Volume dan Rongga','standard'=>'SNI 03-4804-1998','icon'=>'box',
            'fields'=>[['container','Massa bejana','kg'],['full_container','Massa bejana + agregat','kg'],['volume','Volume bejana','cm³'],['specific_gravity','Berat jenis agregat','']],
            'process'=>[['aggregate_mass','E','Massa agregat','B − A','kg'],['bulk_density','BI','Berat isi agregat','E / (C / 1.000.000)','kg/m³'],['voids','R','Rongga udara','(D × 1.000 − BI) / (D × 1.000) × 100','%']]],
        'sieve'=>['label'=>'Analisis Saringan & Modulus Kehalusan','standard'=>'SNI ASTM C136:2012','icon'=>'bar-chart-steps',
            'fields'=>[['sample_mass','Massa sampel awal','g'],['r095','Tertahan 9,5 mm','g'],['r475','Tertahan 4,75 mm','g'],['r236','Tertahan 2,36 mm','g'],['r118','Tertahan 1,18 mm','g'],['r060','Tertahan 0,60 mm','g'],['r030','Tertahan 0,30 mm','g'],['r015','Tertahan 0,15 mm','g'],['pan','Tertahan wadah dasar','g']],
            'process'=>[['retained','C','Massa tertahan','B − A','g'],['retained_percent','D','Persen tertahan','C / total C × 100','%'],['cumulative','E','Persen tertahan kumulatif','Jumlah D berjalan','%'],['passing','F','Persen lolos','100 − E','%'],['fineness_modulus','FM','Modulus kehalusan','Jumlah E pada saringan standar / 100','']]]];
        if($a==='coarse')$base['los-angeles']=['label'=>'Keausan Los Angeles','standard'=>'Metode mesin Los Angeles','icon'=>'gear-wide-connected',
            'fields'=>[['initial','Massa awal benda uji','g'],['retained','Massa tertahan setelah pengujian','g']],
            'process'=>[['mass_loss','C','Massa aus/hilang','A − B','g'],['abrasion','KA','Keausan Los Angeles','C / A × 100','%']]];
        return $base;
    }
}
