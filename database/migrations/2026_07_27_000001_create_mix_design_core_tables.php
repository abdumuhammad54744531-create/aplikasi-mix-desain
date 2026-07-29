<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laboratory_profiles', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('institution')->nullable();
            $table->string('accreditation_number')->nullable(); $table->text('address')->nullable();
            $table->string('phone')->nullable(); $table->string('email')->nullable();
            $table->string('website')->nullable(); $table->string('head_name')->nullable();
            $table->string('head_employee_number')->nullable(); $table->string('head_position')->nullable();
            $table->text('report_footer')->nullable(); $table->timestamps();
        });
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); $table->string('number')->unique(); $table->string('name');
            $table->string('work_package')->nullable(); $table->string('owner')->nullable();
            $table->string('contractor')->nullable(); $table->string('consultant')->nullable();
            $table->text('location')->nullable(); $table->string('contract_number')->nullable();
            $table->date('contract_date')->nullable(); $table->date('start_date')->nullable();
            $table->date('end_date')->nullable(); $table->string('person_in_charge')->nullable();
            $table->string('supervisor')->nullable(); $table->string('concrete_grade')->nullable();
            $table->string('construction_type')->nullable(); $table->string('environment')->nullable();
            $table->text('notes')->nullable(); $table->string('status')->default('aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes(); $table->timestamps(); $table->index(['status', 'name']);
        });
        Schema::create('material_sources', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique(); $table->string('type'); $table->string('name');
            $table->string('brand')->nullable(); $table->string('producer')->nullable();
            $table->string('quarry')->nullable(); $table->string('supplier')->nullable();
            $table->date('sampled_at')->nullable(); $table->string('sample_number')->nullable();
            $table->string('batch_number')->nullable(); $table->string('condition')->nullable();
            $table->text('notes')->nullable(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes(); $table->timestamps(); $table->index(['type', 'name']);
        });
        Schema::create('mix_designs', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique(); $table->unsignedInteger('revision')->default(0);
            $table->date('planned_at'); $table->string('designer'); $table->string('method')->default('SNI 7656:2012');
            $table->string('concrete_type')->default('normal'); $table->decimal('fc', 12, 4);
            $table->unsignedSmallInteger('design_age')->default(28); $table->decimal('standard_deviation', 12, 4)->nullable();
            $table->decimal('fcr', 12, 4)->nullable(); $table->decimal('slump_min', 12, 4);
            $table->decimal('slump_max', 12, 4); $table->decimal('max_aggregate_size', 12, 4);
            $table->decimal('water_cement_ratio', 12, 6)->nullable(); $table->decimal('water_content', 14, 4)->nullable();
            $table->decimal('cement_content', 14, 4)->nullable(); $table->decimal('fine_aggregate', 14, 4)->nullable();
            $table->decimal('coarse_aggregate', 14, 4)->nullable(); $table->decimal('absolute_volume', 14, 6)->nullable();
            $table->string('status')->default('draft'); $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes(); $table->timestamps(); $table->index(['status', 'planned_at']);
        });
        Schema::create('reference_headers', function (Blueprint $table) {
            $table->id(); $table->string('category'); $table->string('name'); $table->string('standard_number')->nullable();
            $table->string('standard_year')->nullable(); $table->date('effective_at')->nullable();
            $table->boolean('is_active')->default(true); $table->text('source_document')->nullable(); $table->timestamps();
        });
        Schema::create('reference_details', function (Blueprint $table) {
            $table->id(); $table->foreignId('reference_header_id')->constrained()->cascadeOnDelete();
            $table->decimal('x_value', 18, 6)->nullable(); $table->decimal('y_value', 18, 6)->nullable();
            $table->decimal('min_value', 18, 6)->nullable(); $table->decimal('max_value', 18, 6)->nullable();
            $table->string('unit')->nullable(); $table->text('notes')->nullable(); $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module'); $table->string('action'); $table->string('record_type')->nullable();
            $table->unsignedBigInteger('record_id')->nullable(); $table->json('before_data')->nullable();
            $table->json('after_data')->nullable(); $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent(); $table->index(['module', 'created_at']);
        });
    }
    public function down(): void
    {
        foreach (['audit_logs','reference_details','reference_headers','mix_designs','material_sources','projects','laboratory_profiles'] as $table) Schema::dropIfExists($table);
    }
};
