<?php

namespace App\Http\Controllers;

use App\Models\{
    AggregateTestRun,
    CementTest,
    CoarseAggregateTest,
    FineAggregateTest,
    LaboratoryWorkflow,
    MaterialSource,
    MixDesign,
    Project,
    TestDocumentation,
    WaterTest
};
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    private const TYPES = [
        'projects' => [Project::class, 'Proyek'],
        'materials' => [MaterialSource::class, 'Sumber Material'],
        'cement-tests' => [CementTest::class, 'Pemeriksaan Semen'],
        'water-tests' => [WaterTest::class, 'Pemeriksaan Air'],
        'fine-aggregate-tests' => [FineAggregateTest::class, 'Pemeriksaan Pasir'],
        'coarse-aggregate-tests' => [CoarseAggregateTest::class, 'Pemeriksaan Kerikil'],
        'aggregate-test-runs' => [AggregateTestRun::class, 'Hasil Uji Agregat'],
        'workflows' => [LaboratoryWorkflow::class, 'Desain Campuran / Kuat Tekan'],
        'mix-designs' => [MixDesign::class, 'Draf Desain Campuran'],
        'documentations' => [TestDocumentation::class, 'Dokumentasi'],
    ];

    public function index()
    {
        $groups = collect(self::TYPES)->map(function ($config, $type) {
            [$model, $label] = $config;
            return [
                'type' => $type,
                'label' => $label,
                'items' => $model::onlyTrashed()->latest('deleted_at')->get(),
            ];
        })->filter(fn ($group) => $group['items']->isNotEmpty())->values();

        return view('archive.index', compact('groups'));
    }

    public function archive(string $type, int $id)
    {
        [$model, $label] = $this->type($type);
        $record = $model::findOrFail($id);
        $before = $record->toArray();
        $record->delete();
        AuditService::record($label, 'pindahkan ke arsip', $record, $before);

        return back()->with('success', "{$label} dipindahkan ke Arsip.");
    }

    public function restore(string $type, int $id)
    {
        [$model, $label] = $this->type($type);
        $record = $model::onlyTrashed()->findOrFail($id);
        $record->restore();
        AuditService::record($label, 'pulihkan dari arsip', $record);

        return back()->with('success', "{$label} berhasil dipulihkan.");
    }

    public function destroy(string $type, int $id)
    {
        [$model, $label] = $this->type($type);
        $record = $model::onlyTrashed()->findOrFail($id);
        $before = $record->toArray();

        DB::transaction(function () use ($record) {
            if ($record instanceof TestDocumentation) {
                Storage::disk('public')->delete($record->photo_path);
            }

            if ($record instanceof Project) {
                $documents = TestDocumentation::withTrashed()
                    ->where('project_id', $record->id)->get();
                foreach ($documents as $document) {
                    Storage::disk('public')->delete($document->photo_path);
                }
            }

            $record->forceDelete();
        });

        AuditService::record($label, 'hapus permanen', $record, $before);

        return back()->with('success', "{$label} dihapus permanen.");
    }

    private function type(string $type): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        return self::TYPES[$type];
    }
}
