<?php
namespace App\Http\Controllers;
use App\Models\{Project,MaterialSource,MixDesign,AuditLog};
class DashboardController extends Controller {
    public function index(){
        if(auth()->user()->role==='pemohon')return redirect()->route('lab-requests.index');
        return view('dashboard',[
        'stats'=>['projects'=>Project::count(),'materials'=>MaterialSource::count(),'mixes'=>MixDesign::count(),
            'drafts'=>MixDesign::where('status','draft')->count(),'approved'=>MixDesign::where('status','disetujui')->count()],
        'recent'=>AuditLog::with('user')->latest('created_at')->limit(7)->get()
    ]); }
}
