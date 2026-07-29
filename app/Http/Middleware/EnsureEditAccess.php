<?php
namespace App\Http\Middleware;
use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
class EnsureEditAccess {
 public function handle(Request $request,Closure $next){
  abort_if(auth()->user()?->access_level==='read'&&!in_array(auth()->user()?->role,['admin','administrator']),403,'Akun ini hanya memiliki akses baca.');
  if(!$request->routeIs('workflow.report.status')){
   $bound=$request->route('project');$project=$bound instanceof Project?$bound:($request->input('project_id')?Project::find($request->input('project_id')):null);
   abort_if($project?->locked_at,423,'Dokumen telah disetujui dan dikunci. Buat revisi baru melalui menu Laporan sebelum mengubah data.');
  }
  return $next($request);
 }
}
