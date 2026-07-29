<?php
namespace App\Http\Controllers;
use App\Models\{MaterialSource,Project};
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class MaterialSourceController extends Controller {
    public function index(){ return view('materials.index',['materials'=>MaterialSource::with('project')->latest()->get(),'projects'=>Project::where('status','aktif')->get()]); }
    public function store(Request $r){
        $data=$r->validate(['project_id'=>'nullable|exists:projects,id','code'=>'required|unique:material_sources,code','type'=>'required',
            'name'=>'required','brand'=>'nullable','producer'=>'nullable','quarry'=>'nullable','supplier'=>'nullable','sampled_at'=>'nullable|date',
            'sample_number'=>'nullable','batch_number'=>'nullable','condition'=>'nullable','notes'=>'nullable']);
        $data['created_by']=$data['updated_by']=auth()->id(); $m=MaterialSource::create($data); AuditService::record('Sumber Material','tambah',$m);
        return back()->with('success','Sumber material berhasil disimpan.');
    }
    public function update(Request $r,MaterialSource $material){
        $before=$material->toArray();
        $data=$r->validate(['project_id'=>'nullable|exists:projects,id','code'=>['required',Rule::unique('material_sources')->ignore($material)],'type'=>'required',
            'name'=>'required','brand'=>'nullable','producer'=>'nullable','quarry'=>'nullable','supplier'=>'nullable','sampled_at'=>'nullable|date',
            'sample_number'=>'nullable','batch_number'=>'nullable','condition'=>'nullable','notes'=>'nullable']);
        $data['updated_by']=auth()->id(); $material->update($data); AuditService::record('Sumber Material','ubah',$material,$before);
        return back()->with('success','Sumber material berhasil diperbarui.');
    }
    public function destroy(MaterialSource $material){ $before=$material->toArray(); $material->delete(); AuditService::record('Sumber Material','hapus',$material,$before); return back()->with('success','Sumber material diarsipkan.'); }
}
