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
   $statusMessage = match ($effectiveStatus) {
    'valid' => 'Dokumen telah disetujui secara elektronik oleh pejabat yang tercantum. Untuk dokumen fisik, dokumen harus berstempel resmi dari Laboratorium.',
    'revisi' => 'Persetujuan ini berasal dari dokumen yang telah direvisi. Gunakan dokumen terbaru.',
    default => 'Dokumen ditolak atau isinya tidak cocok dengan dokumen yang disahkan.',
   };
  @endphp
  <span class="badge status text-bg-{{$statusClass}} align-self-start">{{strtoupper($effectiveStatus)}}</span>
  <h2 class="fw-bold mt-3">Verifikasi Tanda Tangan Elektronik</h2>
  <div class="alert alert-{{$statusClass}}">{{$statusMessage}}</div>
  <div class="row">
   <div class="col-md-5">
    <h5>Identitas pejabat</h5>
    <table class="table">
     <tr><th>Nama</th><td>{{$approval->user->name}}</td></tr>
     <tr><th>NIP/Identitas</th><td>{{$approval->user->employee_number?:'-'}}</td></tr>
     <tr><th>Jabatan</th><td>{{$approval->user->position?:$approval->user->role}}</td></tr>
     <tr><th>Instansi/unit</th><td>{{$approval->user->institution?:'-'}}</td></tr>
     <tr><th>Kewenangan</th><td>{{$approval->user->approval_authority?:ucfirst($approval->approval_role)}}</td></tr>
    </table>
   </div>
   <div class="col-md-7">
    <h5>Data persetujuan</h5>
    <table class="table">
     <tr><th>Jenis persetujuan</th><td>{{$approval->approval_type}}</td></tr>
     <tr><th>Nomor laporan</th><td>{{$project->number}}</td></tr>
     <tr><th>Proyek</th><td>{{$project->name}}</td></tr>
     <tr><th>Jenis laporan</th><td>Desain Campuran Beton</td></tr>
     <tr><th>Jenis desain campuran</th><td>SNI 7656:2012</td></tr>
     <tr><th>Waktu persetujuan</th><td>{{$approval->approved_at?->format('d/m/Y H:i:s')}} WITA</td></tr>
     <tr><th>Kode unik</th><td><code>{{$approval->approval_id}}</code></td></tr>
     <tr><th>Status</th><td><b>{{strtoupper($effectiveStatus)}}</b></td></tr>
    </table>
   </div>
  </div>
  <h5>Riwayat persetujuan</h5>
  <table class="table table-bordered">
   <thead><tr><th>Peran</th><th>Pejabat</th><th>Waktu</th><th>Status</th></tr></thead>
   <tbody>@foreach($project->reportApprovals()->with('user')->orderBy('revision')->orderBy('approved_at')->get() as $history)<tr><td>{{ucfirst($history->approval_role)}}</td><td>{{$history->user->name}}</td><td>{{$history->approved_at?->format('d/m/Y H:i')}}</td><td><b>{{strtoupper($history->status === 'valid' ? 'valid' : ($history->status === 'direvisi' ? 'revisi' : 'dokumen palsu'))}}</b></td></tr>@endforeach</tbody>
  </table>
  <a class="btn btn-success" href="{{route('public.verify',$project->verification_code)}}">Verifikasi keseluruhan laporan</a>
 </div>
</main>
</body>
</html>
