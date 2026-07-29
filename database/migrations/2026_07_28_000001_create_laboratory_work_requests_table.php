<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laboratory_work_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('request_number')->unique();
            $table->string('applicant_name');
            $table->string('institution')->nullable();
            $table->string('phone', 50);
            $table->string('work_name');
            $table->string('service_type');
            $table->string('sample_description');
            $table->unsignedInteger('sample_quantity')->default(1);
            $table->date('requested_date')->nullable();
            $table->text('project_location')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('diajukan');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_work_requests');
    }
};
