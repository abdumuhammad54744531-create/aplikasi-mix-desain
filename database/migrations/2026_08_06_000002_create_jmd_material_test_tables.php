<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function testHeader(Blueprint $table, bool $withAggregateType = false): void
    {
        $table->id();
        $table->foreignId('project_id')->constrained()->cascadeOnDelete();
        $table->foreignId('jmd_project_material_id')->nullable()->constrained()->nullOnDelete();
        $table->string('test_number', 100)->unique();
        $table->string('sample_number', 100)->nullable();
        if ($withAggregateType) {
            $table->string('aggregate_type', 20);
        }
        $table->date('tested_at')->nullable();
        $table->string('technician')->nullable();
        $table->string('status', 30)->default('draft');
        $table->unsignedInteger('revision_number')->default(0);
        $table->json('result_snapshot')->nullable();
        $table->json('standard_snapshot')->nullable();
        $table->text('notes')->nullable();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('approved_at')->nullable();
        $table->softDeletes();
        $table->timestamps();
        $table->index(['project_id', 'status']);
    }

    private function itemFooter(Blueprint $table, string $testColumn, string $indexName): void
    {
        $table->unsignedInteger('observation_number');
        $table->json('calculation_snapshot')->nullable();
        $table->text('validation_message')->nullable();
        $table->timestamps();
        $table->unique([$testColumn, 'observation_number'], $indexName);
    }

    public function up(): void
    {
        Schema::create('moisture_tests', function (Blueprint $table) {
            $this->testHeader($table, true);
        });
        Schema::create('moisture_test_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moisture_test_id')->constrained()->cascadeOnDelete();
            $table->decimal('container_mass', 18, 6);
            $table->decimal('wet_container_mass', 18, 6);
            $table->decimal('dry_container_mass', 18, 6);
            $this->itemFooter($table, 'moisture_test_id', 'moisture_test_observation_unique');
        });

        Schema::create('silt_tests', function (Blueprint $table) {
            $this->testHeader($table, true);
            $table->decimal('limit_percent', 12, 6)->nullable();
        });
        Schema::create('silt_test_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('silt_test_id')->constrained()->cascadeOnDelete();
            $table->decimal('container_mass', 18, 6);
            $table->decimal('before_wash_container_mass', 18, 6);
            $table->decimal('after_wash_container_mass', 18, 6);
            $this->itemFooter($table, 'silt_test_id', 'silt_test_observation_unique');
        });

        Schema::create('fine_aggregate_sg_tests', function (Blueprint $table) {
            $this->testHeader($table);
        });
        Schema::create('fine_aggregate_sg_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fine_aggregate_sg_test_id')->constrained()->cascadeOnDelete();
            $table->decimal('pycnometer_mass', 18, 6);
            $table->decimal('ssd_sample_mass', 18, 6);
            $table->decimal('pycnometer_sample_water_mass', 18, 6);
            $table->decimal('pycnometer_water_mass', 18, 6);
            $table->decimal('oven_dry_sample_mass', 18, 6);
            $this->itemFooter($table, 'fine_aggregate_sg_test_id', 'fine_sg_test_observation_unique');
        });

        Schema::create('coarse_aggregate_sg_tests', function (Blueprint $table) {
            $this->testHeader($table);
        });
        Schema::create('coarse_aggregate_sg_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coarse_aggregate_sg_test_id')->constrained()->cascadeOnDelete();
            $table->decimal('ssd_air_mass', 18, 6);
            $table->decimal('submerged_mass', 18, 6);
            $table->decimal('oven_dry_mass', 18, 6);
            $this->itemFooter($table, 'coarse_aggregate_sg_test_id', 'coarse_sg_test_observation_unique');
        });

        Schema::create('bulk_density_tests', function (Blueprint $table) {
            $this->testHeader($table);
            $table->string('material_type', 30);
            $table->string('selected_mode', 20)->nullable();
            $table->decimal('manual_selected_value', 18, 6)->nullable();
            $table->text('manual_override_reason')->nullable();
        });
        Schema::create('bulk_density_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_density_test_id')->constrained()->cascadeOnDelete();
            $table->string('condition', 20);
            $table->decimal('mould_volume_cm3', 18, 6);
            $table->decimal('mould_mass', 18, 6);
            $table->decimal('filled_mould_mass', 18, 6);
            $this->itemFooter($table, 'bulk_density_test_id', 'bulk_density_observation_unique');
        });

        Schema::create('cement_sg_tests', function (Blueprint $table) {
            $this->testHeader($table);
        });
        Schema::create('cement_sg_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cement_sg_test_id')->constrained()->cascadeOnDelete();
            $table->decimal('bottle_kerosene_mass', 18, 6);
            $table->decimal('bottle_cement_kerosene_mass', 18, 6);
            $table->decimal('initial_reading_ml', 18, 6);
            $table->decimal('final_reading_ml', 18, 6);
            $table->decimal('test_temperature_c', 10, 4)->nullable();
            $table->decimal('water_density', 18, 8)->nullable();
            $this->itemFooter($table, 'cement_sg_test_id', 'cement_sg_observation_unique');
        });

        Schema::create('sieve_tests', function (Blueprint $table) {
            $this->testHeader($table, true);
            $table->decimal('initial_sample_mass', 18, 6);
            $table->decimal('loss_tolerance_percent', 12, 6)->nullable();
            $table->decimal('maximum_size_mm', 12, 4)->nullable();
            $table->decimal('nominal_maximum_size_mm', 12, 4)->nullable();
            $table->string('gradation_zone', 50)->nullable();
            $table->string('nearest_gradation_zone', 50)->nullable();
        });
        Schema::create('sieve_test_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sieve_test_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order');
            $table->string('sieve_label', 50);
            $table->decimal('sieve_size_mm', 12, 6)->nullable();
            $table->boolean('is_pan')->default(false);
            $table->decimal('retained_mass', 18, 6);
            $table->decimal('lower_limit_percent', 12, 6)->nullable();
            $table->decimal('upper_limit_percent', 12, 6)->nullable();
            $table->decimal('planned_passing_percent', 12, 6)->nullable();
            $table->json('calculation_snapshot')->nullable();
            $table->timestamps();
            $table->unique(['sieve_test_id', 'sort_order'], 'sieve_test_sort_unique');
        });

        Schema::create('abrasion_tests', function (Blueprint $table) {
            $this->testHeader($table);
            $table->string('inspection_gradation')->nullable();
            $table->unsignedSmallInteger('steel_ball_count')->nullable();
            $table->unsignedInteger('revolution_count')->nullable();
            $table->decimal('limit_percent', 12, 6)->nullable();
        });
        Schema::create('abrasion_test_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abrasion_test_id')->constrained()->cascadeOnDelete();
            $table->decimal('passing_sieve_mm', 12, 6)->nullable();
            $table->decimal('retained_sieve_mm', 12, 6)->nullable();
            $table->decimal('initial_mass', 18, 6);
            $table->decimal('retained_no12_mass', 18, 6);
            $this->itemFooter($table, 'abrasion_test_id', 'abrasion_test_observation_unique');
        });
    }

    public function down(): void
    {
        foreach ([
            'abrasion_test_items', 'abrasion_tests', 'sieve_test_items', 'sieve_tests',
            'cement_sg_items', 'cement_sg_tests', 'bulk_density_items', 'bulk_density_tests',
            'coarse_aggregate_sg_items', 'coarse_aggregate_sg_tests',
            'fine_aggregate_sg_items', 'fine_aggregate_sg_tests',
            'silt_test_items', 'silt_tests', 'moisture_test_items', 'moisture_tests',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
