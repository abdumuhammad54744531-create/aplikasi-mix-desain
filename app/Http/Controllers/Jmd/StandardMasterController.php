<?php

namespace App\Http\Controllers\Jmd;

use App\Http\Controllers\Controller;
use App\Http\Requests\Jmd\StoreStandardReferenceRequest;
use App\Http\Requests\Jmd\StoreStandardTableRequest;
use App\Http\Requests\Jmd\StoreStandardValueRequest;
use App\Models\StandardReference;
use App\Models\StandardTableHeader;
use App\Models\StandardTableValue;
use App\Services\AuditService;
use App\Services\Jmd\StandardMasterService;
use Illuminate\Http\Request;

class StandardMasterController extends Controller
{
    public function index(Request $request)
    {
        $this->admin();
        $standards = StandardReference::query()->with(['tables' => fn ($query) => $query->with('values')->orderBy('key')])
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search
                ->where('name', 'like', '%'.$request->string('q')->trim().'%')
                ->orWhere('standard_number', 'like', '%'.$request->string('q')->trim().'%')))
            ->orderByDesc('is_active')->latest('id')->paginate(10)->withQueryString();

        return view('jmd.standards.index', [
            'standards' => $standards, 'catalog' => StandardMasterService::CATALOG,
            'activeTableCount' => StandardTableHeader::where('is_active', true)->count(),
            'activeValueCount' => StandardTableValue::where('is_active', true)->count(),
        ]);
    }

    public function storeReference(StoreStandardReferenceRequest $request)
    {
        $standard = StandardReference::create($request->validated() + [
            'revision_number' => 1, 'is_active' => true, 'created_by' => auth()->id(), 'updated_by' => auth()->id(),
        ]);
        AuditService::record('jmd-standard-master', 'create-reference', $standard);

        return back()->with('success', 'Referensi standar berhasil dibuat.');
    }

    public function reviseReference(StoreStandardReferenceRequest $request, StandardReference $standard, StandardMasterService $service)
    {
        abort_unless($standard->is_active, 422, 'Hanya versi referensi aktif yang dapat direvisi.');
        $before = $standard->toArray();
        $revision = $service->reviseReference($standard, $request->validated(), auth()->id());
        AuditService::record('jmd-standard-master', 'revise-reference', $revision, $before);

        return back()->with('success', 'Revisi referensi baru berhasil dibuat tanpa menimpa versi lama.');
    }

    public function toggleReference(StandardReference $standard)
    {
        $this->admin();
        if (! $standard->is_active) {
            abort_if(StandardReference::where('supersedes_id', $standard->id)->exists(), 422, 'Versi yang sudah digantikan tidak dapat diaktifkan kembali.');
        }
        $before = $standard->toArray();
        $standard->update(['is_active' => ! $standard->is_active, 'updated_by' => auth()->id()]);
        AuditService::record('jmd-standard-master', 'toggle-reference', $standard, $before);

        return back()->with('success', 'Status referensi diperbarui.');
    }

    public function storeTable(StoreStandardTableRequest $request, StandardReference $standard)
    {
        abort_unless($standard->is_active, 422, 'Tabel hanya dapat ditambahkan ke referensi aktif.');
        abort_if($standard->tables()->where('key', $request->validated('key'))->where('is_active', true)->exists(), 422, 'Kelompok tabel aktif sudah tersedia; gunakan fitur revisi.');
        $table = $standard->tables()->create($request->validated() + [
            'revision_number' => 1, 'is_active' => true, 'created_by' => auth()->id(), 'updated_by' => auth()->id(),
        ]);
        AuditService::record('jmd-standard-master', 'create-table', $table);

        return back()->with('success', 'Tabel standar berhasil ditambahkan.');
    }

    public function reviseTable(StoreStandardTableRequest $request, StandardTableHeader $table, StandardMasterService $service)
    {
        abort_unless($table->is_active && $table->standard?->is_active, 422, 'Hanya tabel aktif yang dapat direvisi.');
        $before = $table->toArray();
        $revision = $service->reviseTable($table, $request->validated(), auth()->id());
        AuditService::record('jmd-standard-master', 'revise-table', $revision, $before);

        return back()->with('success', 'Revisi tabel baru berhasil dibuat.');
    }

    public function storeValue(StoreStandardValueRequest $request, StandardTableHeader $table)
    {
        abort_unless($table->is_active && $table->standard?->is_active, 422, 'Nilai hanya dapat ditambahkan ke tabel aktif.');
        $value = $table->values()->create($request->validated() + [
            'is_active' => $request->boolean('is_active', true), 'created_by' => auth()->id(), 'updated_by' => auth()->id(),
        ]);
        AuditService::record('jmd-standard-master', 'create-value', $value);

        return back()->with('success', 'Nilai tabel berhasil ditambahkan.');
    }

    public function updateValue(StoreStandardValueRequest $request, StandardTableValue $value, StandardMasterService $service)
    {
        abort_unless($value->is_active && $value->header?->is_active && $value->header?->standard?->is_active, 422, 'Hanya nilai aktif yang dapat direvisi.');
        $before = $value->toArray();
        $revision = $service->reviseValue($value, $request->validated() + ['is_active' => $request->boolean('is_active')], auth()->id());
        AuditService::record('jmd-standard-master', 'revise-value', $revision, $before);

        return back()->with('success', 'Nilai diperbarui pada revisi tabel baru.');
    }

    public function destroyValue(StandardTableValue $value, StandardMasterService $service)
    {
        $this->admin();
        abort_unless($value->header?->is_active && $value->header?->standard?->is_active, 422, 'Hanya nilai pada tabel aktif yang dapat dihentikan.');
        $before = $value->toArray();
        $service->retireValue($value, auth()->id());
        AuditService::record('jmd-standard-master', 'retire-value', $value, $before);

        return back()->with('success', 'Nilai dihentikan pada revisi tabel baru; versi lama tetap tersimpan.');
    }

    private function admin(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['admin', 'administrator'], true), 403);
    }
}
