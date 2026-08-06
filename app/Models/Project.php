<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Project extends Model {
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['contract_date'=>'date','start_date'=>'date','end_date'=>'date','legalized_at'=>'datetime','locked_at'=>'datetime',
        'report_include_mix_design_2012'=>'boolean','report_include_mix_design_2012_combined'=>'boolean'];
    public function aggregateTestRuns(){ return $this->hasMany(AggregateTestRun::class); }
    public function aggregateTestObservations(){ return $this->hasMany(AggregateTestObservation::class); }
    public function laboratoryWorkflows(){ return $this->hasMany(LaboratoryWorkflow::class); }
    public function reportApprovals(){ return $this->hasMany(ReportApproval::class); }
    public function includedMixDesignTypes(): array {
        return array_keys(array_filter([
            'mix-design-2012'=>(bool)$this->report_include_mix_design_2012,
            'mix-design-2012-combined'=>(bool)$this->report_include_mix_design_2012_combined,
        ]));
    }
}
