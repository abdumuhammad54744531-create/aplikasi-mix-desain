@php
    $statusMessage = match ($verificationStatus) {
        'valid' => 'Laporan dan persetujuan masih berlaku.',
        'direvisi' => 'Terdapat versi laporan yang lebih baru.',
        'ditolak' => 'Laporan tidak mendapat persetujuan.',
        'dicabut' => 'Persetujuan laporan telah dicabut.',
        'dibatalkan' => 'Laporan telah dibatalkan.',
        default => 'Isi dokumen berbeda dari versi yang disetujui.',
    };
    $mixTypes = $project->laboratoryWorkflows()
        ->whereIn('type', $project->includedMixDesignTypes())
        ->pluck('type')
        ->map(fn ($type) => $type === 'mix-design-2012-combined'
            ? 'SNI 7656:2012 (Gradasi Gabungan)'
            : 'SNI 7656:2012')
        ->unique()
        ->join(', ');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Verifikasi Laporan {{$project->number}}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background:#edf4f3}
        .verify-card{max-width:900px;margin:5vh auto;border:0;border-radius:18px;box-shadow:0 12px 40px #1233}
        .seal{width:76px;height:76px;border-radius:50%;background:#087c70;color:white;display:grid;place-items:center;font-size:38px}
    </style>
</head>
<body>
<main class="container">
    <div class="card verify-card p-4 p-md-5">
        <div class="d-flex gap-4 align-items-center mb-4">
            <div class="seal">{{$verificationStatus === 'valid' ? '✓' : '!'}}</div>
            <div>
                <span class="badge {{$verificationStatus === 'valid' ? 'text-bg-success' : 'text-bg-danger'}}">{{strtoupper($verificationStatus)}}</span>
                <h2 class="fw-bold mt-2 mb-0">Verifikasi Laporan Laboratorium</h2>
            </div>
        </div>
        <div class="alert {{$verificationStatus === 'valid' ? 'alert-success' : 'alert-danger'}}">
            <b>{{strtoupper($verificationStatus)}}</b> — {{$statusMessage}}
        </div>
        <table class="table">
            <tr><th>Nomor laporan</th><td>{{$project->number}}</td></tr>
            <tr><th>Judul laporan</th><td>Laporan Hasil Desain Campuran Beton</td></tr>
            <tr><th>Proyek</th><td>{{$project->name}}</td></tr>
            <tr><th>Lokasi</th><td>{{$project->location}}</td></tr>
            <tr><th>Jenis desain campuran</th><td>{{$mixTypes ?: 'Belum tersedia'}}</td></tr>
            <tr><th>Tanggal diterbitkan</th><td>{{$project->legalized_at?->format('d/m/Y H:i') ?: 'Belum diterbitkan'}}{{$project->legalized_at ? ' WITA' : ''}}</td></tr>
            <tr><th>Nomor revisi</th><td>{{$project->report_revision}}</td></tr>
            <tr><th>Status dokumen</th><td><b>{{strtoupper($project->document_status)}}</b></td></tr>
            <tr><th>Status validasi</th><td><b>{{strtoupper($verificationStatus)}}</b></td></tr>
            <tr><th>Laboratorium penerbit</th><td>{{\App\Models\LaboratoryProfile::first()?->name ?: 'Laboratorium Bahan dan Struktur'}}</td></tr>
        </table>

        <h5>Pejabat yang menyetujui</h5>
        <table class="table table-bordered">
            <thead><tr><th>Peran</th><th>Nama</th><th>Jabatan</th><th>Waktu persetujuan</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($project->reportApprovals as $approval)
                <tr>
                    <td>{{ucfirst($approval->approval_role)}}</td>
                    <td>{{$approval->user->name}}</td>
                    <td>{{$approval->user->position ?: $approval->user->role}}</td>
                    <td>{{$approval->approved_at?->format('d/m/Y H:i')}} WITA</td>
                    <td><b>{{strtoupper($approval->status)}}</b></td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada rekaman persetujuan pejabat.</td></tr>
            @endforelse
            </tbody>
        </table>

        @if($verificationStatus === 'valid')
            <div class="d-flex flex-wrap gap-2">
                <a href="{{route('public.report', $project->verification_code)}}" class="btn btn-outline-success btn-lg">Lihat Laporan</a>
                <a href="{{route('public.download', $project->verification_code)}}" class="btn btn-success btn-lg">Unduh PDF</a>
            </div>
        @endif
    </div>
</main>
</body>
</html>
