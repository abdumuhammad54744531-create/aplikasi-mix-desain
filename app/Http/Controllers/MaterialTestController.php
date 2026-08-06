<?php

namespace App\Http\Controllers;

use App\Models\{CementTest,CoarseAggregateTest,FineAggregateTest,MaterialSource,Project,WaterTest};
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MaterialTestController extends Controller
{
    private array $types = [
        'cement' => ['Semen', CementTest::class, 'Semen'],
        'water' => ['Air', WaterTest::class, 'Air'],
        'fine-aggregate' => ['Agregat Halus / Pasir', FineAggregateTest::class, 'Pasir'],
        'coarse-aggregate' => ['Agregat Kasar / Kerikil', CoarseAggregateTest::class, 'Batu pecah'],
    ];

    public function index()
    {
        $cards = [];
        foreach ($this->types as $slug => [$label, $model]) {
            $cards[$slug] = ['label' => $label, 'count' => $model::count()];
        }
        return view('material-tests.index', compact('cards'));
    }

    public function create(string $type)
    {
        abort_unless(isset($this->types[$type]), 404);
        [$label,, $materialType] = $this->types[$type];
        $model=$this->types[$type][1];$savedTests=collect();
        foreach($model::latest()->get() as $record){$payload=$record->toArray();$payload['tested_at']=$record->tested_at?->format('Y-m-d');$payload['received_at']=$record->received_at?->format('Y-m-d');
            $exact=$record->project_id.'-'.($record->material_source_id??0);$fallback=$record->project_id.'-any';if(!$savedTests->has($exact))$savedTests->put($exact,$payload);if(!$savedTests->has($fallback))$savedTests->put($fallback,$payload);}
        return view('material-tests.form', [
            'type' => $type, 'label' => $label, 'fields' => $this->fields($type),
            'projects' => Project::where('status','aktif')->orderBy('name')->get(),
            'materials' => MaterialSource::whereIn('type',$type==='coarse-aggregate'?['Kerikil','Batu pecah']:[$materialType])->orderBy('name')->get(),
            'savedTests'=>$savedTests,
        ]);
    }

    public function store(Request $request, string $type)
    {
        abort_unless(isset($this->types[$type]), 404);
        [$label, $model] = $this->types[$type];
        $rules = [
            'test_id'=>'nullable|integer','project_id'=>'required|exists:projects,id', 'material_source_id'=>'nullable|exists:material_sources,id',
            'sample_number'=>'required|max:100', 'received_at'=>'nullable|date', 'tested_at'=>'required|date',
            'technician'=>'required|max:255', 'notes'=>'nullable|max:5000',
            'source_name'=>'nullable|max:255','source_quarry'=>'nullable|max:255','source_supplier'=>'nullable|max:255',
            'source_sample_number'=>'nullable|max:255','source_condition'=>'nullable|max:255',
        ];
        foreach ($this->fields($type) as $field) {
            $rules[$field['name']] = ($field['required'] ? 'required' : 'nullable').'|'.$field['rule'];
        }
        $data = $request->validate($rules);
        $sourceData=['name'=>$data['source_name']??null,'quarry'=>$data['source_quarry']??null,'supplier'=>$data['source_supplier']??null,'sample_number'=>$data['source_sample_number']??null,'condition'=>$data['source_condition']??null];
        unset($data['source_name'],$data['source_quarry'],$data['source_supplier'],$data['source_sample_number'],$data['source_condition']);
        $test=DB::transaction(function()use($data,$sourceData,$model,$type,$label){$testId=$data['test_id']??null;unset($data['test_id']);
            if(!empty($data['material_source_id'])){$source=MaterialSource::findOrFail($data['material_source_id']);abort_unless($source->project_id===null||(int)$source->project_id===(int)$data['project_id'],422);$before=$source->toArray();$source->update([...$sourceData,'updated_by'=>auth()->id()]);AuditService::record('Sumber Material','ubah dari pemeriksaan',$source,$before);}
            $data['updated_by']=auth()->id();if($testId){$test=$model::whereKey($testId)->where('project_id',$data['project_id'])->firstOrFail();$before=$test->toArray();$test->update($data);AuditService::record('Pemeriksaan '.$label,'ubah',$test,$before);return $test;}
            $data['test_number']=strtoupper(substr(str_replace('-','',$type),0,3)).'-'.now()->format('ymd').'-'.str_pad((string)($model::withTrashed()->count()+1),3,'0',STR_PAD_LEFT);$data['created_by']=auth()->id();return $model::create($data);
        }); AuditService::record('Pemeriksaan '.$label,'simpan draf',$test);
        return redirect()->route('material-tests.index')->with('success',"Pemeriksaan {$label} berhasil disimpan sebagai draf.");
    }

    private function fields(string $type): array
    {
        $common = fn(string $name,string $label,string $unit='',bool $required=false,string $rule='numeric|min:0') =>
            compact('name','label','unit','required','rule');
        return match($type) {
            'cement' => [
                $common('cement_type','Jenis semen','',true,'string|max:100'), $common('brand','Merek','','false'==='true','string|max:100'),
                $common('batch_number','Nomor batch','','false'==='true','string|max:100'), $common('color','Warna','','false'==='true','string|max:100'),
                $common('package_condition','Kondisi kemasan','','false'==='true','string|max:100'),
                $common('specific_gravity','Berat jenis','',true), $common('fineness','Kehalusan','%',false),
                $common('normal_consistency','Konsistensi normal','%',false), $common('initial_setting_time','Waktu ikat awal','menit',false),
                $common('final_setting_time','Waktu ikat akhir','menit',false), $common('mortar_strength','Kuat tekan mortar','MPa',false),
                $common('temperature','Suhu semen','°C',false,'numeric'),
            ],
            'water' => [
                $common('water_source','Sumber air','',true,'string|max:255'), $common('sampling_location','Lokasi pengambilan','',false,'string|max:255'),
                $common('color','Warna','',false,'string|max:100'), $common('odor','Bau','',false,'string|max:100'),
                $common('ph','pH','',true,'numeric|between:0,14'), $common('silt_content','Kandungan lumpur','mg/L',false),
                $common('organic_content','Kandungan organik','mg/L',false), $common('chloride','Klorida','mg/L',false),
                $common('sulfate','Sulfat','mg/L',false), $common('dissolved_solids','Zat padat terlarut','mg/L',false),
                $common('comparative_mortar_strength','Kuat tekan mortar pembanding','%',false),
            ],
            'fine-aggregate' => [
                $common('quarry','Lokasi sumber','',false,'string|max:255'), $common('supplier','Pemasok','',false,'string|max:255'),
                $common('bulk_specific_gravity_dry','Berat jenis curah kering','',true), $common('specific_gravity_ssd','Berat jenis SSD','',true),
                $common('apparent_specific_gravity','Berat jenis semu','',false), $common('absorption','Penyerapan air','%',true),
                $common('loose_bulk_density','Berat isi lepas','kg/m³',false), $common('compacted_bulk_density','Berat isi padat','kg/m³',false),
                $common('field_moisture','Kadar air lapangan','%',true), $common('silt_content','Kadar lumpur','%',false),
                $common('fineness_modulus','Modulus kehalusan','',false), $common('gradation_zone','Zona gradasi','',false,'string|max:50'),
                $common('void_percentage','Persentase rongga','%',false), $common('aggregate_condition','Kondisi agregat','',false,'string|max:50'),
            ],
            'coarse-aggregate' => [
                $common('aggregate_type','Jenis agregat','',true,'string|max:100'), $common('quarry','Lokasi sumber','',false,'string|max:255'),
                $common('nominal_maximum_size','Ukuran nominal maksimum','mm',true), $common('bulk_specific_gravity_dry','Berat jenis curah kering','',true),
                $common('specific_gravity_ssd','Berat jenis SSD','',true), $common('apparent_specific_gravity','Berat jenis semu','',false),
                $common('absorption','Penyerapan air','%',true), $common('loose_bulk_density','Berat isi lepas','kg/m³',false),
                $common('compacted_bulk_density','Berat isi padat / tusuk','kg/m³',false), $common('field_moisture','Kadar air lapangan','%',true),
                $common('silt_content','Kadar lumpur','%',false), $common('abrasion','Keausan / abrasi','%',false),
                $common('flakiness','Butir pipih','%',false), $common('elongation','Butir lonjong','%',false),
                $common('crushed_particles','Butir pecah','%',false), $common('void_percentage','Persentase rongga','%',false),
            ],
        };
    }
}
