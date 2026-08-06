<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standard_references', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('standard_number')->nullable();
            $table->string('standard_year', 20)->nullable();
            $table->unsignedInteger('revision_number')->default(1);
            $table->foreignId('supersedes_id')->nullable()->constrained('standard_references')->nullOnDelete();
            $table->date('effective_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['standard_number', 'standard_year', 'revision_number'], 'standard_reference_version_unique');
            $table->index(['is_active', 'effective_at'], 'standard_reference_active_index');
        });

        Schema::create('standard_table_headers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_reference_id')->constrained()->cascadeOnDelete();
            $table->string('key', 100);
            $table->string('name');
            $table->string('unit', 50)->nullable();
            $table->json('dimensions')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('revision_number')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['standard_reference_id', 'key', 'revision_number'], 'standard_table_header_version_unique');
        });

        Schema::create('standard_table_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_table_header_id')->constrained()->cascadeOnDelete();
            $table->string('row_key', 150)->nullable();
            $table->string('column_key', 150)->nullable();
            $table->json('dimension_values')->nullable();
            $table->decimal('numeric_value', 24, 12)->nullable();
            $table->text('text_value')->nullable();
            $table->decimal('min_value', 24, 12)->nullable();
            $table->decimal('max_value', 24, 12)->nullable();
            $table->string('unit', 50)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['standard_table_header_id', 'sort_order'], 'standard_table_value_sort_index');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('jmd_number', 100)->nullable()->unique()->after('number');
            $table->string('report_number', 100)->nullable()->after('jmd_number');
            $table->string('sample_number', 100)->nullable()->after('report_number');
            $table->string('request_letter_number', 100)->nullable()->after('sample_number');
            $table->date('request_letter_date')->nullable()->after('request_letter_number');
            $table->date('materials_received_at')->nullable()->after('request_letter_date');
            $table->date('testing_date')->nullable()->after('materials_received_at');
            $table->date('report_date')->nullable()->after('testing_date');
            $table->string('activity_name')->nullable()->after('name');
            $table->string('city')->nullable()->after('location');
            $table->string('employer')->nullable()->after('owner');
            $table->string('company_name')->nullable()->after('employer');
            $table->string('director_name')->nullable()->after('company_name');
            $table->text('company_address')->nullable()->after('director_name');
            $table->string('tester_name')->nullable()->after('supervisor');
            $table->string('reviewer_name')->nullable()->after('tester_name');
            $table->string('laboratory_head_name')->nullable()->after('reviewer_name');
            $table->string('laboratory_name')->nullable()->after('laboratory_head_name');
            $table->string('work_type')->nullable()->after('construction_type');
            $table->boolean('use_global_institution')->default(true)->after('environment');
            $table->json('institution_snapshot')->nullable()->after('use_global_institution');
            $table->string('laboratory_logo_path')->nullable()->after('institution_snapshot');
            $table->string('university_logo_path')->nullable()->after('laboratory_logo_path');
            $table->string('letterhead_path')->nullable()->after('university_logo_path');
            $table->string('signature_stamp_path')->nullable()->after('letterhead_path');
            $table->string('jmd_status', 60)->default('draft')->after('status');
            $table->unsignedTinyInteger('progress_percent')->default(0)->after('jmd_status');
            $table->json('module_progress')->nullable()->after('progress_percent');
            $table->index(['jmd_status', 'report_date'], 'projects_jmd_status_report_index');
        });

        DB::table('projects')->whereNull('jmd_number')->orderBy('id')->get(['id', 'number'])->each(function ($project) {
            DB::table('projects')->where('id', $project->id)->update(['jmd_number' => 'JMD-'.$project->number]);
        });

        Schema::create('jmd_project_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('material_type', 60);
            $table->string('name')->nullable();
            $table->string('brand')->nullable();
            $table->string('source')->nullable();
            $table->string('supplier')->nullable();
            $table->string('condition')->nullable();
            $table->boolean('use_test_result')->default(true);
            $table->string('selected_bulk_density_mode', 30)->nullable();
            $table->decimal('manual_bulk_density', 18, 6)->nullable();
            $table->text('manual_value_reason')->nullable();
            $table->json('properties')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['project_id', 'material_type'], 'jmd_project_material_type_index');
        });

        Schema::create('jmd_design_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('revision_number')->default(0);
            $table->string('strength_system', 20)->default('fc');
            $table->decimal('specified_strength', 18, 6)->nullable();
            $table->string('specified_strength_unit', 20)->default('MPa');
            $table->decimal('cube_strength', 18, 6)->nullable();
            $table->decimal('cylinder_strength', 18, 6)->nullable();
            $table->decimal('conversion_factor', 18, 8)->nullable();
            $table->string('conversion_method')->nullable();
            $table->text('conversion_basis')->nullable();
            $table->unsignedSmallInteger('design_age_days')->default(28);
            $table->decimal('failure_allowance_percent', 8, 4)->default(5);
            $table->decimal('statistical_factor_k', 12, 6)->nullable();
            $table->decimal('standard_deviation', 18, 6)->nullable();
            $table->decimal('margin', 18, 6)->nullable();
            $table->decimal('target_mean_strength', 18, 6)->nullable();
            $table->string('specimen_type', 30)->default('cylinder');
            $table->decimal('cylinder_diameter_mm', 12, 4)->nullable();
            $table->decimal('cylinder_height_mm', 12, 4)->nullable();
            $table->decimal('cube_size_mm', 12, 4)->nullable();
            $table->decimal('target_slump_mm', 12, 4)->nullable();
            $table->decimal('slump_min_mm', 12, 4)->nullable();
            $table->decimal('slump_max_mm', 12, 4)->nullable();
            $table->string('placement_method')->nullable();
            $table->string('compaction_condition')->nullable();
            $table->decimal('maximum_aggregate_size_mm', 12, 4)->nullable();
            $table->string('cement_type')->nullable();
            $table->string('cement_brand')->nullable();
            $table->decimal('cement_specific_gravity', 12, 6)->nullable();
            $table->decimal('cement_bulk_density', 18, 6)->nullable();
            $table->decimal('cement_bag_weight_kg', 12, 4)->default(50);
            $table->string('fine_aggregate_type')->nullable();
            $table->string('coarse_aggregate_type')->nullable();
            $table->string('fine_aggregate_source')->nullable();
            $table->string('coarse_aggregate_source')->nullable();
            $table->string('water_source')->nullable();
            $table->boolean('uses_admixture')->default(false);
            $table->string('admixture_type')->nullable();
            $table->decimal('admixture_dosage', 18, 8)->nullable();
            $table->string('admixture_dosage_unit', 30)->nullable();
            $table->string('exposure_condition')->nullable();
            $table->json('standard_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['project_id', 'revision_number'], 'jmd_design_project_revision_unique');
        });

        Schema::table('report_settings', function (Blueprint $table) {
            $table->boolean('watermark_enabled')->default(false);
            $table->string('watermark_text')->nullable();
            $table->string('watermark_image')->nullable();
            $table->decimal('watermark_opacity', 5, 4)->default(0.1000);
            $table->string('letterhead_image')->nullable();
            $table->string('stamp_image')->nullable();
            $table->string('report_number_format')->nullable();
            $table->string('decimal_separator', 1)->default(',');
            $table->unsignedTinyInteger('default_decimal_places')->default(2);
            $table->boolean('repeat_table_headers')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('report_settings', function (Blueprint $table) {
            $table->dropColumn([
                'watermark_enabled', 'watermark_text', 'watermark_image', 'watermark_opacity',
                'letterhead_image', 'stamp_image', 'report_number_format', 'decimal_separator',
                'default_decimal_places', 'repeat_table_headers',
            ]);
        });

        Schema::dropIfExists('jmd_design_criteria');
        Schema::dropIfExists('jmd_project_materials');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_jmd_status_report_index');
            $table->dropUnique(['jmd_number']);
            $table->dropColumn([
                'jmd_number', 'report_number', 'sample_number', 'request_letter_number',
                'request_letter_date', 'materials_received_at', 'testing_date', 'report_date',
                'activity_name', 'city', 'employer', 'company_name', 'director_name',
                'company_address', 'tester_name', 'reviewer_name', 'laboratory_head_name',
                'laboratory_name', 'work_type', 'use_global_institution', 'institution_snapshot',
                'laboratory_logo_path', 'university_logo_path', 'letterhead_path',
                'signature_stamp_path', 'jmd_status', 'progress_percent', 'module_progress',
            ]);
        });

        Schema::dropIfExists('standard_table_values');
        Schema::dropIfExists('standard_table_headers');
        Schema::dropIfExists('standard_references');
    }
};
