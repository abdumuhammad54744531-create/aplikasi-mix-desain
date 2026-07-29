<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void { Schema::create('laboratory_workflows',function(Blueprint $t){
  $t->id();$t->foreignId('project_id')->constrained()->cascadeOnDelete();$t->string('type');$t->string('number')->unique();
  $t->date('work_date');$t->json('input_data');$t->json('result_data');$t->string('status')->default('draft');
  $t->text('notes')->nullable();$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();
  $t->index(['project_id','type','work_date']);
 });}
 public function down():void {Schema::dropIfExists('laboratory_workflows');}
};
