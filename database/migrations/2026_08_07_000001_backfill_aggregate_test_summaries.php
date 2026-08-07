<?php

use App\Models\CoarseAggregateTest;
use App\Models\FineAggregateTest;
use App\Services\AggregateTestSummaryService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $groups = DB::table('aggregate_test_runs')
            ->select('project_id', 'material_source_id', 'aggregate_type')
            ->whereNotNull('material_source_id')
            ->whereNull('deleted_at')
            ->distinct()
            ->get();

        $summaryService = app(AggregateTestSummaryService::class);
        foreach ($groups as $group) {
            $model = $group->aggregate_type === 'fine' ? FineAggregateTest::class : CoarseAggregateTest::class;
            $alreadyExists = $model::withTrashed()
                ->where('project_id', $group->project_id)
                ->where('material_source_id', $group->material_source_id)
                ->exists();

            if (! $alreadyExists) {
                $summaryService->sync((int) $group->project_id, (int) $group->material_source_id, $group->aggregate_type);
            }
        }
    }

    public function down(): void
    {
        // Ringkasan adalah data hasil pengujian; rollback struktur tidak boleh menghapus data pengguna.
    }
};
