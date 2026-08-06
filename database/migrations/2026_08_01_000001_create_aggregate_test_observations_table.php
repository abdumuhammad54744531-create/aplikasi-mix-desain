<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aggregate_test_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aggregate_test_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('observation_number');
            $table->json('data');
            $table->timestamps();
            $table->unique(['aggregate_test_run_id', 'observation_number'], 'aggregate_observation_number_unique');
            $table->index(['project_id', 'observation_number']);
        });

        DB::table('aggregate_test_runs')->orderBy('id')->chunkById(100, function ($runs) {
            foreach ($runs as $run) {
                $observations = json_decode($run->observations ?: '[]', true);
                if (!is_array($observations)) continue;
                foreach (array_values($observations) as $index => $data) {
                    if (!is_array($data)) continue;
                    unset($data['id'], $data['observation_number']);
                    DB::table('aggregate_test_observations')->insert([
                        'aggregate_test_run_id' => $run->id,
                        'project_id' => $run->project_id,
                        'observation_number' => $index + 1,
                        'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'created_at' => $run->created_at ?? now(),
                        'updated_at' => $run->updated_at ?? now(),
                    ]);
                }
            }
        }, 'id');
    }

    public function down(): void
    {
        Schema::dropIfExists('aggregate_test_observations');
    }
};
