<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void { Schema::create('aggregate_test_runs',function(Blueprint $t){
        $t->id(); $t->foreignId('project_id')->constrained()->cascadeOnDelete(); $t->foreignId('material_source_id')->nullable()->constrained()->nullOnDelete();
        $t->string('test_number')->unique(); $t->string('aggregate_type'); $t->string('test_type'); $t->string('sample_number');
        $t->date('tested_at'); $t->string('technician'); $t->json('observations'); $t->json('results'); $t->string('status')->default('draft');
        $t->text('notes')->nullable(); $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamps();
        $t->index(['aggregate_type','test_type','tested_at']);
    });}
    public function down():void { Schema::dropIfExists('aggregate_test_runs'); }
};
