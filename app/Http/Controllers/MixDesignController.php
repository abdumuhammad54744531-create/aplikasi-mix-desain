<?php
namespace App\Http\Controllers;
use App\Models\{MixDesign,Project};
use App\Services\AuditService;
use Illuminate\Http\Request;
class MixDesignController extends Controller {
    public function index(){ return view('mix-design.index',['designs'=>MixDesign::with('project')->latest()->get(),'projects'=>Project::where('status','aktif')->get()]); }
    public function store(Request $r){
        $data=$r->validate(['project_id'=>'required|exists:projects,id','planned_at'=>'required|date','designer'=>'required','concrete_type'=>'required',
            'fc'=>'required|numeric|min:0.01','design_age'=>'required|integer|min:1','standard_deviation'=>'nullable|numeric|min:0',
            'slump_min'=>'required|numeric|min:0','slump_max'=>'required|numeric|gte:slump_min','max_aggregate_size'=>'required|numeric|min:0.01','notes'=>'nullable']);
        $data['number']='MD-'.now()->format('ym').'-'.str_pad((string)(MixDesign::withTrashed()->count()+1),3,'0',STR_PAD_LEFT).'-R0';
        $data['fcr']=$data['standard_deviation']!==null ? $data['fc']+1.64*$data['standard_deviation'] : null;
        $data['created_by']=$data['updated_by']=auth()->id(); $m=MixDesign::create($data); AuditService::record('Desain Campuran','simpan draf',$m);
        return back()->with('success','Draf desain campuran tersimpan. Isi tabel referensi resmi sebelum perhitungan komposisi.');
    }
}
