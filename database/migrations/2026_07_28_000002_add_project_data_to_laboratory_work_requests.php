<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('laboratory_work_requests', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('project_number', 50)->nullable()->after('work_name');
            $table->string('work_package')->nullable()->after('project_number');
            $table->string('owner')->nullable()->after('work_package');
            $table->string('contractor')->nullable()->after('owner');
            $table->string('consultant')->nullable()->after('contractor');
            $table->string('contract_number')->nullable()->after('project_location');
            $table->date('contract_date')->nullable()->after('contract_number');
            $table->date('start_date')->nullable()->after('contract_date');
            $table->date('end_date')->nullable()->after('start_date');
            $table->string('person_in_charge')->nullable()->after('end_date');
            $table->string('supervisor')->nullable()->after('person_in_charge');
            $table->string('concrete_grade')->nullable()->after('supervisor');
            $table->string('construction_type')->nullable()->after('concrete_grade');
            $table->string('environment')->nullable()->after('construction_type');
        });
    }

    public function down(): void
    {
        Schema::table('laboratory_work_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn([
                'project_number', 'work_package', 'owner', 'contractor', 'consultant',
                'contract_number', 'contract_date', 'start_date', 'end_date',
                'person_in_charge', 'supervisor', 'concrete_grade',
                'construction_type', 'environment',
            ]);
        });
    }
};
