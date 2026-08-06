<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function actors(Blueprint $table): void
    {
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
    }

    public function up(): void
    {
        Schema::create('mix_design_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jmd_design_criteria_id')->nullable()->constrained('jmd_design_criteria')->nullOnDelete();
            $table->string('calculation_number', 100)->unique();
            $table->unsignedInteger('revision_number')->default(0);
            $table->string('method')->default('SNI 7656:2012');
            $table->string('status', 30)->default('draft');
            $table->json('input_snapshot');
            $table->json('standard_snapshot')->nullable();
            $table->json('raw_result')->nullable();
            $table->json('rounded_result')->nullable();
            $table->json('calculation_log')->nullable();
            $table->json('validation_messages')->nullable();
            $table->decimal('total_absolute_volume', 18, 10)->nullable();
            $table->decimal('volume_correction_factor', 18, 10)->nullable();
            $table->text('volume_correction_reason')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $this->actors($table);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['project_id', 'revision_number'], 'mix_design_project_revision_unique');
            $table->index(['project_id', 'status'], 'mix_design_project_status_index');
        });

        Schema::create('mix_design_material_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mix_design_calculation_id')->constrained()->cascadeOnDelete();
            $table->string('material_code', 40);
            $table->string('material_name')->nullable();
            $table->decimal('ssd_mass_kg', 18, 8)->nullable();
            $table->decimal('field_mass_kg', 18, 8)->nullable();
            $table->decimal('specific_gravity', 18, 8)->nullable();
            $table->decimal('bulk_density_kg_m3', 18, 8)->nullable();
            $table->decimal('absolute_volume_m3', 18, 10)->nullable();
            $table->decimal('weight_ratio', 18, 10)->nullable();
            $table->decimal('volume_ratio', 18, 10)->nullable();
            $table->string('unit', 30)->default('kg/m3');
            $table->json('calculation_snapshot')->nullable();
            $table->timestamps();
            $table->unique(['mix_design_calculation_id', 'material_code'], 'mix_design_material_unique');
        });

        Schema::create('moisture_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mix_design_calculation_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('revision_number')->default(0);
            $table->decimal('fine_moisture_percent', 12, 6);
            $table->decimal('fine_absorption_percent', 12, 6);
            $table->decimal('coarse_moisture_percent', 12, 6);
            $table->decimal('coarse_absorption_percent', 12, 6);
            $table->decimal('fine_ssd_mass_kg', 18, 8);
            $table->decimal('coarse_ssd_mass_kg', 18, 8);
            $table->decimal('design_water_kg', 18, 8);
            $table->decimal('fine_field_mass_kg', 18, 8)->nullable();
            $table->decimal('coarse_field_mass_kg', 18, 8)->nullable();
            $table->decimal('fine_water_correction_kg', 18, 8)->nullable();
            $table->decimal('coarse_water_correction_kg', 18, 8)->nullable();
            $table->decimal('mixer_water_kg', 18, 8)->nullable();
            $table->decimal('effective_water_cement_ratio', 18, 10)->nullable();
            $table->json('calculation_snapshot')->nullable();
            $table->json('validation_messages')->nullable();
            $table->text('notes')->nullable();
            $this->actors($table);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['mix_design_calculation_id', 'revision_number'], 'moisture_correction_revision_unique');
        });

        Schema::create('trial_mixes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mix_design_calculation_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number', 100)->unique();
            $table->dateTime('mixed_at')->nullable();
            $table->string('specimen_type', 30)->default('cylinder');
            $table->unsignedInteger('specimen_count')->default(0);
            $table->decimal('diameter_mm', 12, 4)->nullable();
            $table->decimal('height_mm', 12, 4)->nullable();
            $table->decimal('length_mm', 12, 4)->nullable();
            $table->decimal('width_mm', 12, 4)->nullable();
            $table->decimal('waste_factor', 12, 8)->default(1.2);
            $table->decimal('slump_test_volume_m3', 18, 10)->default(0);
            $table->decimal('manual_extra_volume_m3', 18, 10)->default(0);
            $table->decimal('total_trial_volume_m3', 18, 10)->nullable();
            $table->string('status', 30)->default('draft');
            $table->json('calculation_snapshot')->nullable();
            $table->text('notes')->nullable();
            $this->actors($table);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['project_id', 'status'], 'trial_mix_project_status_index');
        });

        Schema::create('trial_mix_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trial_mix_id')->constrained()->cascadeOnDelete();
            $table->string('material_code', 40);
            $table->decimal('theoretical_mass_kg', 18, 8);
            $table->decimal('weighing_mass_kg', 18, 8)->nullable();
            $table->decimal('rounding_difference_kg', 18, 8)->nullable();
            $table->boolean('weighed')->default(false);
            $table->foreignId('weighed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('weighed_at')->nullable();
            $table->timestamps();
            $table->unique(['trial_mix_id', 'material_code'], 'trial_mix_material_unique');
        });

        Schema::create('slump_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trial_mix_id')->constrained()->cascadeOnDelete();
            $table->string('test_number', 100)->unique();
            $table->dateTime('measured_at')->nullable();
            $table->decimal('target_slump_mm', 12, 4);
            $table->decimal('minimum_slump_mm', 12, 4);
            $table->decimal('maximum_slump_mm', 12, 4);
            $table->decimal('actual_slump_mm', 12, 4);
            $table->decimal('concrete_temperature_c', 10, 4)->nullable();
            $table->decimal('ambient_temperature_c', 10, 4)->nullable();
            $table->unsignedInteger('mixing_duration_seconds')->nullable();
            $table->string('mixture_condition')->nullable();
            $table->boolean('has_segregation')->default(false);
            $table->boolean('has_bleeding')->default(false);
            $table->decimal('added_water_kg', 18, 8)->default(0);
            $table->decimal('added_cement_kg', 18, 8)->default(0);
            $table->decimal('added_admixture_kg', 18, 8)->default(0);
            $table->decimal('actual_water_cement_ratio', 18, 10)->nullable();
            $table->string('status', 40)->nullable();
            $table->json('adjustment_snapshot')->nullable();
            $table->text('notes')->nullable();
            $this->actors($table);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('compressive_strength_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trial_mix_id')->nullable()->constrained()->nullOnDelete();
            $table->string('test_number', 100)->unique();
            $table->decimal('target_strength_mpa', 18, 8);
            $table->unsignedSmallInteger('target_age_days')->default(28);
            $table->string('status', 40)->default('draft');
            $table->json('age_factor_snapshot')->nullable();
            $table->json('statistics_snapshot')->nullable();
            $table->json('validation_messages')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $this->actors($table);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['project_id', 'status'], 'strength_test_project_status_index');
        });

        Schema::create('compressive_strength_specimens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compressive_strength_test_id');
            $table->foreign('compressive_strength_test_id', 'strength_specimens_test_fk')
                ->references('id')->on('compressive_strength_tests')->cascadeOnDelete();
            $table->string('specimen_number', 100);
            $table->string('batch_number', 100)->nullable();
            $table->date('cast_at');
            $table->date('tested_at');
            $table->unsignedSmallInteger('actual_age_days')->nullable();
            $table->string('specimen_type', 30);
            $table->decimal('diameter_mm', 12, 4)->nullable();
            $table->decimal('height_mm', 12, 4)->nullable();
            $table->decimal('length_mm', 12, 4)->nullable();
            $table->decimal('width_mm', 12, 4)->nullable();
            $table->decimal('weight_kg', 18, 8)->nullable();
            $table->decimal('maximum_load', 24, 8);
            $table->string('load_unit', 10);
            $table->string('condition')->nullable();
            $table->string('failure_type')->nullable();
            $table->decimal('compressive_strength_mpa', 18, 8)->nullable();
            $table->decimal('compressive_strength_kg_cm2', 18, 8)->nullable();
            $table->decimal('equivalent_k_strength', 18, 8)->nullable();
            $table->decimal('estimated_28_day_mpa', 18, 8)->nullable();
            $table->json('calculation_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['compressive_strength_test_id', 'specimen_number'], 'strength_specimen_number_unique');
        });

        Schema::create('field_batch_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mix_design_calculation_id')->constrained()->cascadeOnDelete();
            $table->string('conversion_type', 40);
            $table->decimal('target_volume_m3', 18, 10)->nullable();
            $table->decimal('cement_bag_count', 18, 6)->nullable();
            $table->decimal('mixer_capacity_m3', 18, 10)->nullable();
            $table->string('container_shape', 20)->nullable();
            $table->decimal('container_length_m', 18, 8)->nullable();
            $table->decimal('container_width_m', 18, 8)->nullable();
            $table->decimal('container_height_m', 18, 8)->nullable();
            $table->decimal('container_diameter_m', 18, 8)->nullable();
            $table->decimal('manual_container_volume_m3', 18, 10)->nullable();
            $table->string('rounding_method', 30)->nullable();
            $table->json('result_snapshot')->nullable();
            $table->text('notes')->nullable();
            $this->actors($table);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'field_batch_conversions', 'compressive_strength_specimens', 'compressive_strength_tests',
            'slump_tests', 'trial_mix_materials', 'trial_mixes', 'moisture_corrections',
            'mix_design_material_results', 'mix_design_calculations',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
