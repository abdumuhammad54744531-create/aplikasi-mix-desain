<!doctype html>
<html lang="id">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width,initial-scale=1">
 <title>Verifikasi Persetujuan</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
 <style>body{background:#edf4f3}.card{max-width:900px;margin:5vh auto;border:0;border-radius:18px;box-shadow:0 12px 40px #1233}.status{font-size:1.05rem}</style>
</head>
<body>
<main class="container">
 <div class="card p-4 p-md-5">
  @php
   $statusClass = $effectiveStatus === 'valid' ? 'success' : ($effectiveStatus === 'revisi' ? 'warning' : 'danger');
   $statusLabel = $effectiveStatus === 'revisi' ? 'DI REVISI' : strtoupper($effectiveStatus);
   $statusMessage = match ($effectiveStatus) {
    'valid' => 'Dokumen telah disetujui secara elektronik oleh pejabat yang tercantum. Untuk dokumen fisik, dokumen harus berstempel resmi dari Laboratorium.',
    'revisi' => 'Dokumen sedang direvisi. Gunakan hasil verifikasi terbaru setelah revisi disetujui.',
    default => 'Dokumen telah ditolak dan tidak berlaku.',
   };
  @endphp
  <span class="badge status text-bg-{{$statusClass}} align-self-start">{{$statusLabel}}</span>
  <h2 class="fw-bold mt-3">Verifikasi Tanda Tangan Elektronik</h2>
  <div class="alert alert-{{$statusClass}}">{{$statusMessage}}</div>
  <table class="table">
   <tr><th>Nomor laporan</th><td>{{$project->number}}</td></tr>
   <tr><th>Nama pejabat</th><td>{{$approval->user->name}}</td></tr>
   <tr><th>Jabatan</th><td>{{$approval->user->position?:$approval->user->role}}</td></tr>
   <tr><th>Waktu persetujuan terakhir</th><td>{{$approval->approved_at?->format('d/m/Y H:i')}} WITA</td></tr>
   <tr><th>Status terbaru</th><td><b>{{$statusLabel}}</b></td></tr>
  </table>
  <a class="btn btn-success" href="{{route('public.verify',$project->verification_code)}}">Verifikasi keseluruhan laporan</a>
 </div>
</main>
</body>
</html>
