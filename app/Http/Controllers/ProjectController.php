<?php
namespace App\Http\Controllers;
use App\Models\Project;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class ProjectController extends Controller {
    public function index(){ return view('projects.index',['projects'=>Project::latest()->get()]); }
    public function store(Request $r){
        $data=$r->validate(['number'=>'nullable|max:50|unique:projects,number','name'=>'required|max:255','status'=>'nullable|in:aktif,selesai',
            'owner'=>'nullable|max:255','location'=>'nullable','contract_number'=>'nullable|max:255','construction_type'=>'nullable|max:255',
            'report_include_mix_design_2012'=>'nullable|boolean','report_include_mix_design_2012_combined'=>'nullable|boolean']);
        $data['number']=$data['number']??$this->nextNumber();$data['status']=$data['status']??'aktif';
        $hasReportChoice=$r->has('report_include_mix_design_2012')||$r->has('report_include_mix_design_2012_combined');
        $data['report_include_mix_design_2012']=$hasReportChoice?$r->boolean('report_include_mix_design_2012'):true;
        $data['report_include_mix_design_2012_combined']=$hasReportChoice?$r->boolean('report_include_mix_design_2012_combined'):true;
        $data['created_by']=$data['updated_by']=auth()->id(); $model=Project::create($data); AuditService::record('Proyek','tambah',$model);
        return back()->with('success','Data proyek berhasil disimpan.');
    }
    public function update(Request $r, Project $project){
        $before=$project->toArray(); $data=$r->validate([
            'number'=>['nullable','max:50',Rule::unique('projects')->ignore($project)],'name'=>'required|max:255','status'=>'nullable|in:aktif,selesai',
            'owner'=>'nullable|max:255','location'=>'nullable','contract_number'=>'nullable|max:255','construction_type'=>'nullable|max:255',
            'report_include_mix_design_2012'=>'nullable|boolean','report_include_mix_design_2012_combined'=>'nullable|boolean'
        ]);
        unset($data['number'],$data['status'],$data['report_include_mix_design_2012'],$data['report_include_mix_design_2012_combined']);
        $data['updated_by']=auth()->id(); $project->update($data); AuditService::record('Proyek','ubah',$project,$before);
        return back()->with('success','Data proyek berhasil diperbarui.');
    }
    public function destroy(Project $project){ $before=$project->toArray(); $project->delete(); AuditService::record('Proyek','hapus',$project,$before); return back()->with('success','Data proyek dipindahkan ke arsip.'); }
    private function nextNumber():string{$prefix='PRJ-'.now()->format('ymd').'-';$next=Project::withTrashed()->where('number','like',$prefix.'%')->count()+1;do{$number=$prefix.str_pad((string)$next,3,'0',STR_PAD_LEFT);$next++;}while(Project::withTrashed()->where('number',$number)->exists());return $number;}
}
