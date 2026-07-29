<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('report_include_mix_design_2012')->default(true)->after('notes');
            $table->boolean('report_include_mix_design_2012_combined')->default(true)->after('report_include_mix_design_2012');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'report_include_mix_design_2012',
                'report_include_mix_design_2012_combined',
            ]);
        });
    }
};
