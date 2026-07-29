<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function identity(Blueprint $table): void
    {
        $table->id();
        $table->foreignId('project_id')->constrained()->cascadeOnDelete();
        $table->foreignId('material_source_id')->nullable()->constrained()->nullOnDelete();
        $table->string('test_number')->unique();
        $table->string('sample_number');
        $table->date('received_at')->nullable();
        $table->date('tested_at');
        $table->string('technician');
        $table->string('status')->default('draft');
        $table->text('notes')->nullable();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->softDeletes();
        $table->timestamps();
    }

    public function up(): void
    {
        Schema::create('cement_tests', function (Blueprint $table) {
            $this->identity($table);
            $table->string('cement_type')->nullable(); $table->string('brand')->nullable();
            $table->string('batch_number')->nullable(); $table->string('color')->nullable();
            $table->string('package_condition')->nullable(); $table->boolean('has_lumps')->nullable();
            $table->decimal('specific_gravity', 12, 4)->nullable(); $table->decimal('fineness', 12, 4)->nullable();
            $table->decimal('normal_consistency', 12, 4)->nullable(); $table->decimal('initial_setting_time', 12, 4)->nullable();
            $table->decimal('final_setting_time', 12, 4)->nullable(); $table->decimal('mortar_strength', 12, 4)->nullable();
            $table->decimal('temperature', 12, 4)->nullable();
        });
        Schema::create('water_tests', function (Blueprint $table) {
            $this->identity($table);
            $table->string('water_source')->nullable(); $table->string('sampling_location')->nullable();
            $table->date('sampled_at')->nullable(); $table->string('color')->nullable(); $table->string('odor')->nullable();
            $table->decimal('ph', 8, 3)->nullable(); $table->decimal('silt_content', 14, 4)->nullable();
            $table->decimal('organic_content', 14, 4)->nullable(); $table->decimal('chloride', 14, 4)->nullable();
            $table->decimal('sulfate', 14, 4)->nullable(); $table->decimal('dissolved_solids', 14, 4)->nullable();
            $table->decimal('comparative_mortar_strength', 14, 4)->nullable();
        });
        Schema::create('fine_aggregate_tests', function (Blueprint $table) {
            $this->identity($table);
            $table->string('quarry')->nullable(); $table->string('supplier')->nullable();
            $table->decimal('bulk_specific_gravity_dry', 12, 4)->nullable(); $table->decimal('specific_gravity_ssd', 12, 4)->nullable();
            $table->decimal('apparent_specific_gravity', 12, 4)->nullable(); $table->decimal('absorption', 12, 4)->nullable();
            $table->decimal('loose_bulk_density', 14, 4)->nullable(); $table->decimal('compacted_bulk_density', 14, 4)->nullable();
            $table->decimal('field_moisture', 12, 4)->nullable(); $table->decimal('silt_content', 12, 4)->nullable();
            $table->decimal('fineness_modulus', 12, 4)->nullable(); $table->string('gradation_zone')->nullable();
            $table->decimal('void_percentage', 12, 4)->nullable(); $table->string('aggregate_condition')->nullable();
        });
        Schema::create('coarse_aggregate_tests', function (Blueprint $table) {
            $this->identity($table);
            $table->string('aggregate_type')->nullable(); $table->string('quarry')->nullable();
            $table->decimal('nominal_maximum_size', 12, 4)->nullable();
            $table->decimal('bulk_specific_gravity_dry', 12, 4)->nullable(); $table->decimal('specific_gravity_ssd', 12, 4)->nullable();
            $table->decimal('apparent_specific_gravity', 12, 4)->nullable(); $table->decimal('absorption', 12, 4)->nullable();
            $table->decimal('loose_bulk_density', 14, 4)->nullable(); $table->decimal('compacted_bulk_density', 14, 4)->nullable();
            $table->decimal('field_moisture', 12, 4)->nullable(); $table->decimal('silt_content', 12, 4)->nullable();
            $table->decimal('abrasion', 12, 4)->nullable(); $table->decimal('flakiness', 12, 4)->nullable();
            $table->decimal('elongation', 12, 4)->nullable(); $table->decimal('crushed_particles', 12, 4)->nullable();
            $table->decimal('void_percentage', 12, 4)->nullable();
        });
    }

    public function down(): void
    {
        foreach (['coarse_aggregate_tests','fine_aggregate_tests','water_tests','cement_tests'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
