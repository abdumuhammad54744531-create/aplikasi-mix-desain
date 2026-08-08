<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index()
    {
        return view('projects.index', ['projects' => Project::latest()->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['location'] = $data['location_address'] ?? $data['location'] ?? null;
        $data['number'] = $data['number'] ?? $this->nextNumber();
        $data['status'] = $data['status'] ?? 'aktif';
        $hasReportChoice = $request->has('report_include_mix_design_2012') || $request->has('report_include_mix_design_2012_combined');
        $data['report_include_mix_design_2012'] = $hasReportChoice ? $request->boolean('report_include_mix_design_2012') : true;
        $data['report_include_mix_design_2012_combined'] = $hasReportChoice ? $request->boolean('report_include_mix_design_2012_combined') : true;
        $data['map_image'] = $this->storeMap($request);
        $data['created_by'] = $data['updated_by'] = auth()->id();
        $project = Project::create($data);
        AuditService::record('Proyek', 'tambah', $project);

        return back()->with('success', 'Data proyek dan lokasi berhasil disimpan.');
    }

    public function update(Request $request, Project $project)
    {
        $before = $project->toArray();
        $data = $this->validated($request, $project);
        $data['location'] = $data['location_address'] ?? $data['location'] ?? $project->location;
        unset($data['number'], $data['status'], $data['report_include_mix_design_2012'], $data['report_include_mix_design_2012_combined']);
        if ($request->hasFile('map_image')) {
            $newMap = $this->storeMap($request);
            if ($project->map_image) {
                Storage::disk('public')->delete($project->map_image);
            }
            $data['map_image'] = $newMap;
        }
        $data['updated_by'] = auth()->id();
        $project->update($data);
        AuditService::record('Proyek', 'ubah', $project, $before);

        return back()->with('success', 'Data proyek dan lokasi berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        $before = $project->toArray();
        $project->delete();
        AuditService::record('Proyek', 'hapus', $project, $before);

        return back()->with('success', 'Data proyek dipindahkan ke arsip.');
    }

    private function validated(Request $request, ?Project $project = null): array
    {
        return $request->validate([
            'number' => ['nullable', 'max:50', Rule::unique('projects')->ignore($project)],
            'name' => 'required|max:255', 'status' => 'nullable|in:aktif,selesai',
            'work_package' => 'nullable|max:255', 'owner' => 'nullable|max:255',
            'contractor' => 'nullable|max:255', 'consultant' => 'nullable|max:255',
            'location' => 'nullable', 'contract_number' => 'nullable|max:255',
            'contract_date' => 'nullable|date', 'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'concrete_grade' => 'nullable|max:255', 'construction_type' => 'nullable|max:255',
            'location_description' => 'nullable|max:5000', 'location_address' => 'nullable|max:5000',
            'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180',
            'coordinate_format' => 'nullable|in:decimal,dms', 'map_image' => 'nullable|image|max:8192',
            'map_caption' => 'nullable|max:255',
            'report_include_mix_design_2012' => 'nullable|boolean',
            'report_include_mix_design_2012_combined' => 'nullable|boolean',
        ]);
    }

    private function storeMap(Request $request): ?string
    {
        if (! $request->hasFile('map_image')) {
            return null;
        }
        $file = $request->file('map_image');
        return $file->storeAs('project-maps', Str::uuid().'.'.strtolower($file->extension() ?: 'png'), 'public');
    }

    private function nextNumber(): string
    {
        $prefix = 'PRJ-'.now()->format('ymd').'-';
        $next = Project::withTrashed()->where('number', 'like', $prefix.'%')->count() + 1;
        do {
            $number = $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $next++;
        } while (Project::withTrashed()->where('number', $number)->exists());

        return $number;
    }
}
