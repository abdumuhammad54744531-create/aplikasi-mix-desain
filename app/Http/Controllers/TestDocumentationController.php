<?php

namespace App\Http\Controllers;

use App\Models\{Project, TestDocumentation};
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TestDocumentationController extends Controller
{
    public const MODULES = [
        'cement' => 'Pemeriksaan Semen',
        'water' => 'Pemeriksaan Air',
        'fine-aggregate' => 'Pemeriksaan Pasir',
        'coarse-aggregate' => 'Pemeriksaan Kerikil',
        'mix-design-2012' => 'Desain Campuran SNI 7656:2012',
        'mix-design-2012-combined' => 'Desain Campuran SNI 7656:2012 (Gradasi Gabungan)',
        'compressive-strength' => 'Pengujian Kuat Tekan',
    ];

    public function index(Request $request)
    {
        $projects = Project::orderByDesc('updated_at')->get();
        $selectedProject = $request->integer('project') ?: $projects->first()?->id;
        $selectedModule = array_key_exists($request->module, self::MODULES) ? $request->module : 'fine-aggregate';
        $documents = TestDocumentation::where('project_id', $selectedProject)
            ->where('module', $selectedModule)->orderBy('sort_order')->latest()->get();

        return view('documentation.index', compact('projects', 'selectedProject', 'selectedModule', 'documents') + ['modules' => self::MODULES]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'module' => ['required', Rule::in(array_keys(self::MODULES))],
            'title' => 'required|max:255',
            'documented_at' => 'nullable|date',
            'description' => 'nullable|max:2000',
            'photos' => 'required|array|min:1|max:12',
            'photos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        foreach ($request->file('photos') as $index => $photo) {
            $document = TestDocumentation::create([
                'project_id' => $data['project_id'],
                'module' => $data['module'],
                'title' => count($request->file('photos')) > 1 ? $data['title'].' '.($index + 1) : $data['title'],
                'documented_at' => $data['documented_at'] ?? now(),
                'description' => $data['description'] ?? null,
                'photo_path' => $photo->store('documentation/'.$data['project_id'], 'public'),
                'sort_order' => TestDocumentation::where('project_id', $data['project_id'])->where('module', $data['module'])->max('sort_order') + 1,
                'created_by' => auth()->id(),
            ]);
            AuditService::record('Dokumentasi '.self::MODULES[$data['module']], 'unggah foto', $document);
        }

        return redirect()->route('documentation.index', ['project' => $data['project_id'], 'module' => $data['module']])
            ->with('success', 'Dokumentasi pemeriksaan berhasil disimpan dan akan masuk ke laporan akhir.');
    }

    public function destroy(TestDocumentation $documentation)
    {
        $project = $documentation->project_id;
        $module = $documentation->module;
        $before = $documentation->toArray();
        $documentation->delete();
        AuditService::record('Dokumentasi '.(self::MODULES[$module] ?? $module), 'pindahkan ke arsip', $documentation, $before);
        return redirect()->route('documentation.index', ['project' => $project, 'module' => $module])
            ->with('success', 'Foto dokumentasi dipindahkan ke Arsip.');
    }
}
