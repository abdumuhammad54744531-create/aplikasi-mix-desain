<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('test_documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('module', 60);
            $table->string('title');
            $table->date('documented_at')->nullable();
            $table->text('description')->nullable();
            $table->string('photo_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['project_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_documentations');
    }
};
