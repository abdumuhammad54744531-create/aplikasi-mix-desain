<?php

namespace App\Http\Controllers;

use App\Models\LaboratoryWorkRequest;
use App\Models\LaboratoryProfile;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LaboratoryWorkRequestController extends Controller
{
    public const SERVICES = [
        'pemeriksaan-material' => 'Pemeriksaan karakteristik material',
        'analisis-agregat' => 'Analisa saringan saja',
        'desain-campuran' => 'Mix desain dan mix formula',
        'kuat-tekan' => 'Pengujian kuat tekan beton',
        'paket-lengkap' => 'Pemeriksaan lengkap',
        'lainnya' => 'Lainnya',
    ];

    public function brochure()
    {
        return view('lab-requests.brochure', [
            'services' => self::SERVICES,
            'laboratory' => LaboratoryProfile::first(),
        ]);
    }

    public function index()
    {
        $user = auth()->user();
        $query = LaboratoryWorkRequest::with(['user','project'])->latest();
        if ($user->role === 'pemohon') {
            $query->where('user_id', $user->id);
        }

        return view($user->role === 'pemohon' ? 'lab-requests.applicant' : 'lab-requests.index', [
            'requests' => $query->get(),
            'services' => self::SERVICES,
            'isApplicant' => $user->role === 'pemohon',
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->role === 'pemohon', 403, 'Hanya akun pemohon yang dapat mengirim permohonan pengujian.');
        $data = $request->validate([
            'phone' => 'required|string|max:50',
            'institution' => 'required|string|max:255',
            'work_name' => 'required|string|max:255',
            'project_number' => 'required|string|max:50',
            'work_package' => 'required|string|max:255',
            'owner' => 'required|string|max:255',
            'contractor' => 'required|string|max:255',
            'consultant' => 'required|string|max:255',
            'service_type' => ['required', Rule::in(array_keys(self::SERVICES))],
            'sample_description' => 'required|string|max:255',
            'sample_quantity' => 'required|integer|min:1|max:100000',
            'requested_date' => 'required|date|after_or_equal:today',
            'project_location' => 'required|string|max:1000',
            'contract_number' => 'required|string|max:255',
            'contract_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'person_in_charge' => 'nullable|string|max:255',
            'supervisor' => 'nullable|string|max:255',
            'concrete_grade' => 'required|string|max:255',
            'construction_type' => 'required|string|max:255',
            'environment' => 'required|string|max:255',
            'description' => 'nullable|string|max:3000',
            'application_letter' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $letterPath = $request->file('application_letter')
            ->store('application-letters/'.now()->format('Y'), 'public');
        unset($data['application_letter']);
        $record = LaboratoryWorkRequest::create([
            ...$data,
            'application_letter_path' => $letterPath,
            'user_id' => auth()->id(),
            'request_number' => 'PLAB-'.now()->format('Ymd-His').'-'.auth()->id(),
            'applicant_name' => auth()->user()->name,
            'status' => 'diajukan',
        ]);
        auth()->user()->update(['institution' => $data['institution'] ?: auth()->user()->institution]);

        return redirect()->route('lab-requests.index')
            ->with('success', "Permohonan {$record->request_number} berhasil dikirim ke laboratorium.");
    }

    public function updateStatus(Request $request, LaboratoryWorkRequest $labRequest)
    {
        abort_if(auth()->user()->role === 'pemohon', 403);
        $data = $request->validate([
            'status' => 'required|in:diajukan,ditinjau,perlu-perbaikan,diterima,dijadwalkan,selesai,ditolak',
            'admin_notes' => 'nullable|string|max:3000',
        ]);
        $labRequest->update($data);

        return back()->with('success', "Status {$labRequest->request_number} berhasil diperbarui.");
    }

    public function approveAndCreateProject(LaboratoryWorkRequest $labRequest)
    {
        abort_if(auth()->user()->role === 'pemohon', 403);

        $project = DB::transaction(function () use ($labRequest) {
            $record = LaboratoryWorkRequest::lockForUpdate()->findOrFail($labRequest->id);
            if ($record->project_id) {
                return Project::findOrFail($record->project_id);
            }

            $number = $record->project_number;
            if (Project::withTrashed()->where('number', $number)->exists()) {
                $suffix = '-P'.$record->id;
                $number = mb_substr($number, 0, 50 - mb_strlen($suffix)).$suffix;
            }

            $project = Project::create([
                'number' => $number,
                'name' => $record->work_name,
                'work_package' => $record->work_package,
                'owner' => $record->owner,
                'contractor' => $record->contractor,
                'consultant' => $record->consultant,
                'location' => $record->project_location,
                'contract_number' => $record->contract_number,
                'contract_date' => $record->contract_date,
                'start_date' => $record->start_date,
                'end_date' => $record->end_date,
                'person_in_charge' => $record->person_in_charge,
                'supervisor' => $record->supervisor,
                'concrete_grade' => $record->concrete_grade,
                'construction_type' => $record->construction_type,
                'environment' => $record->environment,
                'notes' => trim("Dibuat otomatis dari permohonan {$record->request_number}.\n".$record->description),
                'status' => 'aktif',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            $record->update([
                'project_id' => $project->id,
                'status' => 'diterima',
                'admin_notes' => $record->admin_notes ?: 'Permohonan disetujui dan Data Proyek telah dibuat.',
            ]);

            return $project;
        });

        return redirect()->route('projects.index', ['edit' => $project->id])
            ->with('success', "Permohonan disetujui. Proyek {$project->number} dibuat otomatis dan siap diperiksa kembali.");
    }
}
