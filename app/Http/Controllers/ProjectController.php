<?php
namespace App\Http\Controllers;
use App\Models\Project;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class ProjectController extends Controller {
    public function index(){ return view('projects.index',['projects'=>Project::latest()->get()]); }
    public function store(Request $r){
        $data=$r->validate(['number'=>'required|max:50|unique:projects,number','name'=>'required|max:255','status'=>'required|in:aktif,selesai',
            'work_package'=>'nullable','owner'=>'nullable','contractor'=>'nullable','consultant'=>'nullable','location'=>'nullable',
            'contract_number'=>'nullable','contract_date'=>'nullable|date','start_date'=>'nullable|date','end_date'=>'nullable|date|after_or_equal:start_date',
            'person_in_charge'=>'nullable','supervisor'=>'nullable','concrete_grade'=>'nullable','construction_type'=>'nullable','environment'=>'nullable','notes'=>'nullable',
            'report_include_mix_design_2012'=>'nullable|boolean','report_include_mix_design_2012_combined'=>'nullable|boolean']);
        $data['report_include_mix_design_2012']=$r->boolean('report_include_mix_design_2012');
        $data['report_include_mix_design_2012_combined']=$r->boolean('report_include_mix_design_2012_combined');
        if(!$data['report_include_mix_design_2012']&&!$data['report_include_mix_design_2012_combined'])return back()->withInput()->withErrors(['report_mix_design'=>'Pilih sedikitnya satu jenis desain campuran untuk dimasukkan ke laporan.']);
        $data['created_by']=$data['updated_by']=auth()->id(); $model=Project::create($data); AuditService::record('Proyek','tambah',$model);
        return back()->with('success','Data proyek berhasil disimpan.');
    }
    public function update(Request $r, Project $project){
        $before=$project->toArray(); $data=$r->validate([
            'number'=>['required','max:50',Rule::unique('projects')->ignore($project)],'name'=>'required|max:255','status'=>'required|in:aktif,selesai',
            'work_package'=>'nullable','owner'=>'nullable','contractor'=>'nullable','consultant'=>'nullable','location'=>'nullable',
            'contract_number'=>'nullable','contract_date'=>'nullable|date','start_date'=>'nullable|date','end_date'=>'nullable|date|after_or_equal:start_date',
            'person_in_charge'=>'nullable','supervisor'=>'nullable','concrete_grade'=>'nullable','construction_type'=>'nullable','environment'=>'nullable','notes'=>'nullable',
            'report_include_mix_design_2012'=>'nullable|boolean','report_include_mix_design_2012_combined'=>'nullable|boolean'
        ]);
        $data['report_include_mix_design_2012']=$r->boolean('report_include_mix_design_2012');
        $data['report_include_mix_design_2012_combined']=$r->boolean('report_include_mix_design_2012_combined');
        if(!$data['report_include_mix_design_2012']&&!$data['report_include_mix_design_2012_combined'])return back()->withInput()->withErrors(['report_mix_design'=>'Pilih sedikitnya satu jenis desain campuran untuk dimasukkan ke laporan.']);
        $data['updated_by']=auth()->id(); $project->update($data); AuditService::record('Proyek','ubah',$project,$before);
        return back()->with('success','Data proyek berhasil diperbarui.');
    }
    public function destroy(Project $project){ $before=$project->toArray(); $project->delete(); AuditService::record('Proyek','hapus',$project,$before); return back()->with('success','Data proyek dipindahkan ke arsip.'); }
}
