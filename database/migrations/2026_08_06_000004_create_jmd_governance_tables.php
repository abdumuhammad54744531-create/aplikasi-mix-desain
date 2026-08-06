<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jmd_manual_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('module', 80);
            $table->string('field_name', 100);
            $table->string('record_type')->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('original_value')->nullable();
            $table->json('override_value');
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->foreignId('overridden_by')->constrained('users');
            $table->timestamp('overridden_at');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'module'], 'jmd_override_project_module_index');
        });

        Schema::create('jmd_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('revision_number');
            $table->foreignId('parent_revision_id')->nullable()->constrained('jmd_revisions')->nullOnDelete();
            $table->string('status', 30)->default('draft');
            $table->text('reason');
            $table->json('calculation_snapshot')->nullable();
            $table->json('standard_snapshot')->nullable();
            $table->json('report_snapshot')->nullable();
            $table->string('snapshot_hash', 64)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['project_id', 'revision_number'], 'jmd_project_revision_unique');
        });

        Schema::create('jmd_eligibility_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jmd_revision_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module', 80);
            $table->string('criterion_key', 100);
            $table->string('status', 30);
            $table->json('actual_value')->nullable();
            $table->json('required_value')->nullable();
            $table->text('basis')->nullable();
            $table->text('message')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['project_id', 'jmd_revision_id', 'criterion_key'], 'jmd_eligibility_criterion_unique');
        });

        Schema::create('jmd_conclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jmd_revision_id')->nullable()->constrained()->nullOnDelete();
            $table->longText('automatic_conclusion')->nullable();
            $table->longText('edited_conclusion')->nullable();
            $table->longText('recommendations')->nullable();
            $table->json('generation_snapshot')->nullable();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('edited_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['project_id', 'jmd_revision_id'], 'jmd_conclusion_revision_unique');
        });

        Schema::create('jmd_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('category', 80);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('photographed_at')->nullable();
            $table->string('original_path');
            $table->string('processed_path')->nullable();
            $table->smallInteger('rotation_degrees')->default(0);
            $table->json('crop_data')->nullable();
            $table->unsignedTinyInteger('compression_quality')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['project_id', 'category', 'sort_order'], 'jmd_photo_project_category_index');
        });

        Schema::create('jmd_audit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jmd_revision_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module', 80);
            $table->string('field_name', 100)->nullable();
            $table->json('report_value')->nullable();
            $table->json('application_value')->nullable();
            $table->json('difference_value')->nullable();
            $table->text('suspected_cause')->nullable();
            $table->text('recommendation')->nullable();
            $table->text('user_decision')->nullable();
            $table->string('status', 30)->default('open');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'status'], 'jmd_audit_project_status_index');
        });

        Schema::table('report_approvals', function (Blueprint $table) {
            $table->foreignId('jmd_revision_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
            $table->json('authority_snapshot')->nullable()->after('approval_type');
            $table->string('content_snapshot_hash', 64)->nullable()->after('document_hash');
        });
    }

    public function down(): void
    {
        Schema::table('report_approvals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jmd_revision_id');
            $table->dropColumn(['authority_snapshot', 'content_snapshot_hash']);
        });

        foreach ([
            'jmd_audit_notes', 'jmd_photos', 'jmd_conclusions', 'jmd_eligibility_checks',
            'jmd_revisions', 'jmd_manual_overrides',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
