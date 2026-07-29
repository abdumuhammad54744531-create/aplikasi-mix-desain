<?php
namespace App\Http\Controllers;
use App\Models\{AggregateTestRun,MaterialSource,Project};
use App\Services\{AggregateTestCalculator,AuditService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AggregateTestController extends Controller {
    public function worksheet(string $aggregate){
        abort_unless(in_array($aggregate,['fine','coarse']),404);
        $savedRuns=collect();$latestRuns=AggregateTestRun::where('aggregate_type',$aggregate)->latest()->get();
        foreach($latestRuns as $run){$payload=[
                'number'=>$run->test_number,'sample_number'=>$run->sample_number,'tested_at'=>$run->tested_at->format('Y-m-d'),
                'technician'=>$run->technician,'notes'=>$run->notes,'observations'=>$run->observations,'results'=>$run->results,
            ];$exactKey=$run->project_id.'-'.($run->material_source_id??0).'-'.$run->test_type;$fallbackKey=$run->project_id.'-any-'.$run->test_type;if(!$savedRuns->has($exactKey))$savedRuns->put($exactKey,$payload);if(!$savedRuns->has($fallbackKey))$savedRuns->put($fallbackKey,$payload);}
        return view('aggregate-tests.worksheet',['aggregate'=>$aggregate,'tests'=>$this->tests($aggregate),
            'projects'=>Project::where('status','aktif')->get(),
            'materials'=>MaterialSource::whereIn('type',$aggregate==='fine'?['Pasir']:['Kerikil','Batu pecah'])->get(),
            'savedRuns'=>$savedRuns]);
    }
    public function storeWorksheet(Request $r,string $aggregate,AggregateTestCalculator $calculator){
        abort_unless(in_array($aggregate,['fine','coarse']),404); $tests=$this->tests($aggregate);
        $data=$r->validate(['project_id'=>'required|exists:projects,id','material_source_id'=>'nullable|exists:material_sources,id',
            'sample_number'=>'required','tested_at'=>'required|date','technician'=>'required','runs'=>'required|array','notes'=>'nullable',
            'source_name'=>'nullable|max:255','source_quarry'=>'nullable|max:255','source_supplier'=>'nullable|max:255','source_sample_number'=>'nullable|max:255','source_condition'=>'nullable|max:255']);
        $sourceData=['name'=>$data['source_name']??null,'quarry'=>$data['source_quarry']??null,'supplier'=>$data['source_supplier']??null,'sample_number'=>$data['source_sample_number']??null,'condition'=>$data['source_condition']??null];
        unset($data['source_name'],$data['source_quarry'],$data['source_supplier'],$data['source_sample_number'],$data['source_condition']);
        if(!empty($data['material_source_id'])){$source=MaterialSource::findOrFail($data['material_source_id']);abort_unless($source->project_id===null||(int)$source->project_id===(int)$data['project_id'],422);$before=$source->toArray();$source->update([...$sourceData,'updated_by'=>auth()->id()]);AuditService::record('Sumber Material','ubah dari pemeriksaan agregat',$source,$before);}
        $created=DB::transaction(function()use($data,$tests,$aggregate,$calculator){
            $items=[]; foreach($tests as $type=>$config){$obs=$data['runs'][$type]['observations']??[];
                $result=$calculator->calculate($aggregate,$type,$obs);
                $items[]=AggregateTestRun::create(['project_id'=>$data['project_id'],'material_source_id'=>$data['material_source_id']??null,
                    'sample_number'=>$data['sample_number'],'tested_at'=>$data['tested_at'],'technician'=>$data['technician'],
                    'notes'=>$data['notes']??null,'observations'=>$obs,'results'=>$result,'aggregate_type'=>$aggregate,'test_type'=>$type,
                    'test_number'=>strtoupper(substr($aggregate,0,1).substr($type,0,3)).'-'.now()->format('ymdHis').'-'.count($items),
                    'created_by'=>auth()->id()]);}
            return $items;
        });
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
        return view('aggregate-tests.form',['aggregate'=>$aggregate,'test'=>$test,'config'=>$tests[$test],
            'projects'=>Project::where('status','aktif')->get(),
            'materials'=>MaterialSource::whereIn('type',$aggregate==='fine'?['Pasir']:['Kerikil','Batu pecah'])->get()]);
    }
    public function store(Request $r,string $aggregate,string $test,AggregateTestCalculator $calculator){
        $tests=$this->tests($aggregate); abort_unless(isset($tests[$test]),404);
        $data=$r->validate(['project_id'=>'required|exists:projects,id','material_source_id'=>'nullable|exists:material_sources,id',
            'sample_number'=>'required','tested_at'=>'required|date','technician'=>'required','observations'=>'required|array|min:1','observations.*'=>'array','notes'=>'nullable']);
        try{$result=$calculator->calculate($aggregate,$test,$data['observations']);}catch(\InvalidArgumentException $e){return back()->withInput()->withErrors(['observations'=>$e->getMessage()]);}
        $run=AggregateTestRun::create([...$data,'test_number'=>strtoupper(substr($aggregate,0,1).substr($test,0,3)).'-'.now()->format('ymdHis'),
            'aggregate_type'=>$aggregate,'test_type'=>$test,'results'=>$result,'created_by'=>auth()->id()]);
        AuditService::record('Pengujian '.$tests[$test]['label'],'hitung dan simpan',$run);
        return view('aggregate-tests.result',['run'=>$run,'config'=>$tests[$test]]);
    }
    private function tests(string $a):array {
        $base=[
        'moisture'=>['label'=>'Kadar Air','standard'=>'SNI 1971:2011','icon'=>'droplet-half','fields'=>[['container','Massa wadah','g'],['wet_container','Wadah + benda uji sebelum oven','g'],['dry_container','Wadah + benda uji setelah oven','g']]],
        'silt'=>['label'=>'Kadar Lumpur','standard'=>'Metode pencucian','icon'=>'water','fields'=>[['dry_before','Massa kering sebelum pencucian','g'],['dry_after','Massa kering setelah pencucian','g']]],
        'specific-gravity'=>['label'=>'Berat Jenis dan Penyerapan','standard'=>$a==='fine'?'SNI 1970:2016':'SNI 1969:2016','icon'=>'speedometer','fields'=>$a==='fine'?
            [['ssd','Massa benda uji SSD (S)','g'],['pyc_water','Massa piknometer + air (B)','g'],['pyc_sample_water','Massa piknometer + sampel + air (C)','g'],['oven_dry','Massa kering oven (A)','g']]:
            [['oven_dry','Massa kering oven (A)','g'],['ssd','Massa SSD di udara (B)','g'],['submerged','Massa dalam air (C)','g']]],
        'bulk-density'=>['label'=>'Berat Isi, Volume dan Rongga','standard'=>'SNI 03-4804-1998','icon'=>'box','fields'=>[['container','Massa bejana','kg'],['full_container','Massa bejana + agregat','kg'],['volume','Volume bejana','cm³'],['specific_gravity','Berat jenis agregat','']]],
        'sieve'=>['label'=>'Analisis Saringan & Modulus Kehalusan','standard'=>'SNI ASTM C136:2012','icon'=>'bar-chart-steps','fields'=>[['sample_mass','Massa sampel awal','g'],['r475','Tertahan 4,75 mm','g'],['r236','Tertahan 2,36 mm','g'],['r118','Tertahan 1,18 mm','g'],['r060','Tertahan 0,60 mm','g'],['r030','Tertahan 0,30 mm','g'],['r015','Tertahan 0,15 mm','g'],['pan','Tertahan wadah dasar','g']]]];
        if($a==='coarse')$base['los-angeles']=['label'=>'Keausan Los Angeles','standard'=>'Metode mesin Los Angeles','icon'=>'gear-wide-connected','fields'=>[['initial','Massa awal benda uji','g'],['retained','Massa tertahan setelah pengujian','g']]];
        return $base;
    }
}
