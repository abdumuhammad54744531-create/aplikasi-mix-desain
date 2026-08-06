<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JmdSchemaMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_jmd_schema_is_additive_and_contains_required_relational_modules(): void
    {
        $this->assertTrue(Schema::hasColumns('projects', [
            'number', 'jmd_number', 'report_number', 'jmd_status', 'progress_percent',
            'module_progress', 'institution_snapshot', 'locked_at',
        ]));

        foreach ([
            'standard_references', 'standard_table_headers', 'standard_table_values',
            'jmd_project_materials', 'jmd_design_criteria',
            'moisture_tests', 'moisture_test_items', 'silt_tests', 'silt_test_items',
            'fine_aggregate_sg_tests', 'fine_aggregate_sg_items',
            'coarse_aggregate_sg_tests', 'coarse_aggregate_sg_items',
            'bulk_density_tests', 'bulk_density_items', 'cement_sg_tests', 'cement_sg_items',
            'sieve_tests', 'sieve_test_items', 'abrasion_tests', 'abrasion_test_items',
            'mix_design_calculations', 'mix_design_material_results', 'moisture_corrections',
            'trial_mixes', 'trial_mix_materials', 'slump_tests',
            'compressive_strength_tests', 'compressive_strength_specimens',
            'field_batch_conversions', 'jmd_manual_overrides', 'jmd_revisions',
            'jmd_eligibility_checks', 'jmd_conclusions', 'jmd_photos', 'jmd_audit_notes',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} harus tersedia.");
        }

        $this->assertTrue(Schema::hasColumns('moisture_test_items', [
            'moisture_test_id', 'observation_number', 'container_mass',
            'wet_container_mass', 'dry_container_mass', 'calculation_snapshot',
        ]));
        $this->assertTrue(Schema::hasColumns('mix_design_calculations', [
            'project_id', 'jmd_design_criteria_id', 'revision_number', 'input_snapshot',
            'standard_snapshot', 'raw_result', 'rounded_result', 'calculation_log',
        ]));
        $this->assertTrue(Schema::hasColumns('compressive_strength_specimens', [
            'compressive_strength_test_id', 'specimen_number', 'cast_at', 'tested_at',
            'maximum_load', 'load_unit', 'calculation_snapshot',
        ]));
        $this->assertTrue(Schema::hasColumns('report_approvals', [
            'jmd_revision_id', 'authority_snapshot', 'content_snapshot_hash',
        ]));
    }
}
