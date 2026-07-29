<?php
namespace App\Http\Controllers;
use App\Models\{AggregateTestRun,CementTest,CoarseAggregateTest,FineAggregateTest,LaboratoryWorkflow,Project,WaterTest};
class MaterialResultController extends Controller {
 public function index(){return view('material-results.index',['projects'=>Project::withCount(['aggregateTestRuns','laboratoryWorkflows'])->orderByDesc('updated_at')->get()]);}
 public function project(Project $project){
  $materials=collect([
   'Semen'=>CementTest::where('project_id',$project->id)->latest()->get(),
   'Air'=>WaterTest::where('project_id',$project->id)->latest()->get(),
   'Pasir'=>FineAggregateTest::where('project_id',$project->id)->latest()->get(),
   'Kerikil'=>CoarseAggregateTest::where('project_id',$project->id)->latest()->get(),
  ]);
  $runs=AggregateTestRun::where('project_id',$project->id)->latest()->get()->groupBy(fn($r)=>$r->tested_at->format('Y-m-d').'|'.$r->sample_number.'|'.$r->aggregate_type);
  $mixDesigns=LaboratoryWorkflow::where('project_id',$project->id)->whereIn('type',['mix-design-2012','mix-design-2012-combined'])->latest()->get();
  $strengthTests=LaboratoryWorkflow::where('project_id',$project->id)->where('type','compressive-strength')->latest()->get();
  return view('material-results.project',compact('project','materials','runs','mixDesigns','strengthTests'));
 }
}
