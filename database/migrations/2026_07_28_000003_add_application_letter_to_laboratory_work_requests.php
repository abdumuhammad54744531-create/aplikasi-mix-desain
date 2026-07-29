<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('laboratory_work_requests', function (Blueprint $table) {
            $table->string('application_letter_path')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('laboratory_work_requests', function (Blueprint $table) {
            $table->dropColumn('application_letter_path');
        });
    }
};
