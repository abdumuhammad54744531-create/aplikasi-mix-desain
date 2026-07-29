<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up():void {
  Schema::table('users',function(Blueprint $t){$t->string('employee_number')->nullable();$t->string('position')->nullable();$t->string('institution')->nullable();$t->string('approval_authority')->nullable();$t->string('photo_path')->nullable();});
  Schema::table('projects',function(Blueprint $t){$t->unsignedInteger('report_revision')->default(0);$t->string('document_status')->default('draft');$t->string('document_hash',64)->nullable();$t->timestamp('locked_at')->nullable();});
  Schema::create('report_approvals',function(Blueprint $t){$t->id();$t->uuid('approval_id')->unique();$t->string('verification_token',80)->unique();$t->foreignId('project_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->constrained();$t->unsignedInteger('revision')->default(0);$t->string('approval_role');$t->string('approval_type')->default('pengesahan laporan');$t->string('status')->default('valid');$t->string('document_hash',64);$t->string('ip_address',45)->nullable();$t->text('user_agent')->nullable();$t->timestamp('approved_at')->nullable();$t->timestamp('revoked_at')->nullable();$t->text('notes')->nullable();$t->timestamps();});
 }
 public function down():void {
  Schema::dropIfExists('report_approvals');
  Schema::table('projects',fn(Blueprint $t)=>$t->dropColumn(['report_revision','document_status','document_hash','locked_at']));
  Schema::table('users',fn(Blueprint $t)=>$t->dropColumn(['employee_number','position','institution','approval_authority','photo_path']));
 }
};
