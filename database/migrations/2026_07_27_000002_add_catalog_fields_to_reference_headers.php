<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reference_headers', function (Blueprint $table) {
            $table->string('status')->default('Berlaku')->after('standard_year');
            $table->string('catalog_url', 500)->nullable()->after('source_document');
            $table->unique('standard_number');
            $table->index(['category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('reference_headers', function (Blueprint $table) {
            $table->dropUnique(['standard_number']);
            $table->dropIndex(['category', 'status']);
            $table->dropColumn(['status', 'catalog_url']);
        });
    }
};
