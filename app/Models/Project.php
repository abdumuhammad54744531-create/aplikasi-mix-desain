<?php

namespace App\Models;

use App\Enums\JmdStatus;
use App\Models\Jmd\AbrasionTest;
use App\Models\Jmd\AuditNote;
use App\Models\Jmd\BulkDensityTest;
use App\Models\Jmd\CementSpecificGravityTest;
use App\Models\Jmd\CoarseAggregateSpecificGravityTest;
use App\Models\Jmd\CompressiveStrengthTest;
use App\Models\Jmd\Conclusion;
use App\Models\Jmd\DesignCriterion;
use App\Models\Jmd\EligibilityCheck;
use App\Models\Jmd\FieldBatchConversion;
use App\Models\Jmd\FineAggregateSpecificGravityTest;
use App\Models\Jmd\ManualOverride;
use App\Models\Jmd\MixDesignCalculation;
use App\Models\Jmd\MoistureCorrection;
use App\Models\Jmd\MoistureTest;
use App\Models\Jmd\Photo;
use App\Models\Jmd\ProjectMaterial;
use App\Models\Jmd\Revision;
use App\Models\Jmd\SieveTest;
use App\Models\Jmd\SiltTest;
use App\Models\Jmd\SlumpTest;
use App\Models\Jmd\TrialMix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['contract_date' => 'date', 'start_date' => 'date', 'end_date' => 'date', 'legalized_at' => 'datetime', 'locked_at' => 'datetime',
        'report_include_mix_design_2012' => 'boolean', 'report_include_mix_design_2012_combined' => 'boolean',
        'request_letter_date' => 'date', 'materials_received_at' => 'date', 'testing_date' => 'date', 'report_date' => 'date',
        'latitude' => 'decimal:7', 'longitude' => 'decimal:7',
        'use_global_institution' => 'boolean', 'institution_snapshot' => 'array', 'module_progress' => 'array', 'jmd_status' => JmdStatus::class];

    public function aggregateTestRuns()
    {
        return $this->hasMany(AggregateTestRun::class);
    }

    public function aggregateTestObservations()
    {
        return $this->hasMany(AggregateTestObservation::class);
    }

    public function laboratoryWorkflows()
    {
        return $this->hasMany(LaboratoryWorkflow::class);
    }

    public function reportApprovals()
    {
        return $this->hasMany(ReportApproval::class);
    }

    public function jmdMaterials()
    {
        return $this->hasMany(ProjectMaterial::class);
    }

    public function designCriteria()
    {
        return $this->hasMany(DesignCriterion::class);
    }

    public function moistureTests()
    {
        return $this->hasMany(MoistureTest::class);
    }

    public function siltTests()
    {
        return $this->hasMany(SiltTest::class);
    }

    public function fineAggregateSpecificGravityTests()
    {
        return $this->hasMany(FineAggregateSpecificGravityTest::class);
    }

    public function coarseAggregateSpecificGravityTests()
    {
        return $this->hasMany(CoarseAggregateSpecificGravityTest::class);
    }

    public function bulkDensityTests()
    {
        return $this->hasMany(BulkDensityTest::class);
    }

    public function cementSpecificGravityTests()
    {
        return $this->hasMany(CementSpecificGravityTest::class);
    }

    public function sieveTests()
    {
        return $this->hasMany(SieveTest::class);
    }

    public function abrasionTests()
    {
        return $this->hasMany(AbrasionTest::class);
    }

    public function mixDesignCalculations()
    {
        return $this->hasMany(MixDesignCalculation::class);
    }

    public function moistureCorrections()
    {
        return $this->hasMany(MoistureCorrection::class);
    }

    public function trialMixes()
    {
        return $this->hasMany(TrialMix::class);
    }

    public function slumpTests()
    {
        return $this->hasMany(SlumpTest::class);
    }

    public function compressiveStrengthTests()
    {
        return $this->hasMany(CompressiveStrengthTest::class);
    }

    public function fieldBatchConversions()
    {
        return $this->hasMany(FieldBatchConversion::class);
    }

    public function jmdOverrides()
    {
        return $this->hasMany(ManualOverride::class);
    }

    public function jmdRevisions()
    {
        return $this->hasMany(Revision::class);
    }

    public function eligibilityChecks()
    {
        return $this->hasMany(EligibilityCheck::class);
    }

    public function jmdConclusions()
    {
        return $this->hasMany(Conclusion::class);
    }

    public function jmdPhotos()
    {
        return $this->hasMany(Photo::class);
    }

    public function jmdAuditNotes()
    {
        return $this->hasMany(AuditNote::class);
    }

    public function includedMixDesignTypes(): array
    {
        return array_keys(array_filter([
            'mix-design-2012' => (bool) $this->report_include_mix_design_2012,
            'mix-design-2012-combined' => (bool) $this->report_include_mix_design_2012_combined,
        ]));
    }
}
