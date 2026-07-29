<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_settings', function (Blueprint $table) {
            $table->string('logo_left_position', 10)->default('left');
            $table->string('logo_right_position', 10)->default('right');
            $table->decimal('logo_left_width', 5, 1)->default(18);
            $table->decimal('logo_right_width', 5, 1)->default(18);
        });
    }

    public function down(): void
    {
        Schema::table('report_settings', function (Blueprint $table) {
            $table->dropColumn(['logo_left_position', 'logo_right_position', 'logo_left_width', 'logo_right_width']);
        });
    }
};
