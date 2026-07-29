<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{
  Schema::table('users',fn(Blueprint $t)=>$t->string('access_level')->default('edit')->after('role'));
  Schema::table('projects',function(Blueprint $t){$t->uuid('verification_code')->nullable()->unique()->after('status');$t->timestamp('legalized_at')->nullable();$t->foreignId('legalized_by')->nullable()->constrained('users')->nullOnDelete();});
  Schema::create('report_settings',function(Blueprint $t){$t->id();$t->decimal('margin_top',5,2)->default(16);$t->decimal('margin_right',5,2)->default(14);$t->decimal('margin_bottom',5,2)->default(18);$t->decimal('margin_left',5,2)->default(14);$t->string('font_family')->default('Arial');$t->decimal('font_size',4,1)->default(11);$t->string('logo_left')->nullable();$t->string('logo_right')->nullable();$t->string('signature_image')->nullable();$t->string('signer_name')->nullable();$t->string('signer_position')->nullable();$t->text('preface_template')->nullable();$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('report_settings');Schema::table('projects',fn(Blueprint $t)=>$t->dropConstrainedForeignId('legalized_by'));Schema::table('projects',fn(Blueprint $t)=>$t->dropColumn(['verification_code','legalized_at']));Schema::table('users',fn(Blueprint $t)=>$t->dropColumn('access_level'));}
};
