<?php
namespace App\Http\Controllers;
use App\Models\{MaterialSource,Project};
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class MaterialSourceController extends Controller {
    public function index(Request $r){$materials=MaterialSource::with('project')->when($r->filled('project'),fn($q)=>$r->project==='general'?$q->whereNull('project_id'):$q->where('project_id',$r->integer('project')))->when($r->filled('type'),fn($q)=>$q->where('type',$r->type))->when($r->filled('search'),fn($q)=>$q->where(fn($sub)=>$sub->where('code','like','%'.$r->search.'%')->orWhere('name','like','%'.$r->search.'%')->orWhere('notes','like','%'.$r->search.'%')))->latest()->get();return view('materials.index',['materials'=>$materials,'projects'=>Project::where('status','aktif')->orderBy('name')->get(),'filterProjects'=>Project::whereHas('aggregateTestRuns')->orWhereHas('laboratoryWorkflows')->orWhereHas('aggregateTestObservations')->orWhereIn('id',MaterialSource::whereNotNull('project_id')->select('project_id'))->orderBy('name')->get(),'types'=>MaterialSource::select('type')->distinct()->orderBy('type')->pluck('type')]); }
    public function store(Request $r){
        $data=$r->validate(['project_id'=>'nullable|exists:projects,id','code'=>'required|unique:material_sources,code','type'=>'required',
            'name'=>'required','notes'=>'nullable']);
        $data['created_by']=$data['updated_by']=auth()->id(); $m=MaterialSource::create($data); AuditService::record('Sumber Material','tambah',$m);
        return back()->with('success','Sumber material berhasil disimpan.');
    }
    public function update(Request $r,MaterialSource $material){
        $before=$material->toArray();
        $data=$r->validate(['project_id'=>'nullable|exists:projects,id','code'=>['required',Rule::unique('material_sources')->ignore($material)],'type'=>'required',
            'name'=>'required','notes'=>'nullable']);
        $data['updated_by']=auth()->id(); $material->update($data); AuditService::record('Sumber Material','ubah',$material,$before);
        return back()->with('success','Sumber material berhasil diperbarui.');
    }
    public function destroy(MaterialSource $material){ $before=$material->toArray(); $material->delete(); AuditService::record('Sumber Material','hapus',$material,$before); return back()->with('success','Sumber material diarsipkan.'); }
}
