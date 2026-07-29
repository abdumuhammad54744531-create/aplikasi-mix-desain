<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Portal Permohonan Pengujian Laboratorium</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{--navy:#0b2638;--teal:#087f74;--pale:#e6f5f1;--ink:#18323e}
body{background:#f2f6f7;color:var(--ink);font-family:Inter,system-ui,sans-serif}.portal-nav{background:#fff;border-bottom:1px solid #dce6e8;position:sticky;top:0;z-index:20}.brand-mark{width:43px;height:43px;background:var(--teal);color:#fff;border-radius:12px;display:grid;place-items:center;font-size:22px}.hero{background:linear-gradient(120deg,#0b2638,#08756d);color:#fff;padding:38px 0}.card{border:0;border-radius:14px;box-shadow:0 5px 22px #16333e12}.section-title{font-weight:700;border-bottom:1px solid #dce6e8;padding-bottom:9px;margin-top:12px}.form-label{font-weight:600;font-size:.86rem}.badge-soft{background:var(--pale);color:var(--teal)}.upload-box{border:2px dashed #8eb9b4;background:#f4fbf9;border-radius:12px;padding:18px}.footer{background:#0b2638;color:#b9cbd1;padding:25px 0;margin-top:45px}
</style>
</head>
<body>
<nav class="portal-nav"><div class="container py-2 d-flex align-items-center gap-3"><a href="{{route('lab-services.brochure')}}" class="text-decoration-none text-reset d-flex gap-3 align-items-center"><span class="brand-mark"><i class="bi bi-buildings"></i></span><span><b>PORTAL PEMOHON PENGUJIAN</b><small class="d-block text-secondary">Laboratorium Beton Universitas Muhammadiyah Buton</small></span></a><div class="ms-auto d-flex align-items-center gap-3"><a class="btn btn-sm btn-outline-success" href="{{route('lab-services.brochure')}}"><i class="bi bi-layout-text-window me-1"></i>Brosur</a><div class="text-end d-none d-md-block"><b class="small">{{auth()->user()->name}}</b><div class="text-secondary" style="font-size:.72rem">{{auth()->user()->institution?:auth()->user()->username}}</div></div><form method="post" action="{{route('logout')}}">@csrf<button class="btn btn-sm btn-outline-secondary"><i class="bi bi-box-arrow-right me-1"></i>Keluar</button></form></div></div></nav>

<header class="hero"><div class="container"><div class="row align-items-center g-3"><div class="col-lg-8"><div class="small text-warning fw-bold">HALAMAN KHUSUS PEMOHON</div><h1 class="fw-bold mb-2">Permohonan Pengujian Laboratorium</h1><p class="text-white-50 mb-0">Lengkapi data proyek dan unggah surat permohonan. Data baru masuk ke petugas laboratorium setelah tombol kirim ditekan.</p></div><div class="col-lg-4 text-lg-end"><span class="badge rounded-pill text-bg-light p-3"><i class="bi bi-shield-check text-success me-2"></i>Akun Pemohon</span></div></div></div></header>

<main class="container py-4">
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if(session('info'))<div class="alert alert-info">{{session('info')}}</div>@endif
@if($errors->any())<div class="alert alert-danger"><b>Periksa kembali data:</b><ul class="mb-0">@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif

<div class="card p-4 mb-4">
 <div class="d-flex justify-content-between align-items-start gap-3 mb-3"><div><h3 class="fw-bold mb-1">Formulir Permohonan Baru</h3><p class="text-secondary mb-0">Kolom bertanda <span class="text-danger fw-bold">*</span> wajib diisi. Data berikut akan digunakan untuk membuat Data Proyek setelah disetujui.</p></div><span class="badge badge-soft">Belum dikirim</span></div>
 <form method="post" action="{{route('lab-requests.store')}}" enctype="multipart/form-data" class="row g-3">@csrf
  <div class="col-12"><h5 class="section-title">A. Identitas Pemohon</h5></div>
  <div class="col-md-4"><label class="form-label">Nama pemohon <span class="text-danger">*</span></label><input class="form-control" value="{{auth()->user()->name}}" disabled></div>
  <div class="col-md-4"><label class="form-label">Instansi/Perusahaan <span class="text-danger">*</span></label><input name="institution" class="form-control" value="{{old('institution',auth()->user()->institution)}}" required></div>
  <div class="col-md-4"><label class="form-label">Nomor telepon/WhatsApp <span class="text-danger">*</span></label><input name="phone" class="form-control" value="{{old('phone')}}" required></div>

  <div class="col-12"><h5 class="section-title">B. Data Lengkap Proyek</h5></div>
  <div class="col-md-4"><label class="form-label">Nomor proyek <span class="text-danger">*</span></label><input name="project_number" class="form-control" value="{{old('project_number')}}" required></div>
  <div class="col-md-8"><label class="form-label">Nama proyek/pekerjaan <span class="text-danger">*</span></label><input name="work_name" class="form-control" value="{{old('work_name')}}" required></div>
  <div class="col-md-6"><label class="form-label">Paket pekerjaan <span class="text-danger">*</span></label><input name="work_package" class="form-control" value="{{old('work_package')}}" required></div>
  <div class="col-md-6"><label class="form-label">Pemilik pekerjaan <span class="text-danger">*</span></label><input name="owner" class="form-control" value="{{old('owner')}}" required></div>
  <div class="col-md-6"><label class="form-label">Kontraktor pelaksana <span class="text-danger">*</span></label><input name="contractor" class="form-control" value="{{old('contractor')}}" required></div>
  <div class="col-md-6"><label class="form-label">Konsultan <span class="text-danger">*</span></label><input name="consultant" class="form-control" value="{{old('consultant')}}" required></div>
  <div class="col-12"><label class="form-label">Lokasi proyek <span class="text-danger">*</span></label><textarea name="project_location" class="form-control" rows="2" required>{{old('project_location')}}</textarea></div>
  <div class="col-md-6"><label class="form-label">Nomor kontrak <span class="text-danger">*</span></label><input name="contract_number" class="form-control" value="{{old('contract_number')}}" required></div>
  <div class="col-md-6"><label class="form-label">Tanggal kontrak <span class="text-danger">*</span></label><input type="date" name="contract_date" class="form-control" value="{{old('contract_date')}}" required></div>
  <div class="col-md-4"><label class="form-label">Tanggal mulai <span class="text-danger">*</span></label><input type="date" name="start_date" class="form-control" value="{{old('start_date')}}" required></div>
  <div class="col-md-4"><label class="form-label">Tanggal selesai <span class="text-danger">*</span></label><input type="date" name="end_date" class="form-control" value="{{old('end_date')}}" required></div>
  <div class="col-md-4"><label class="form-label">Penanggung jawab proyek</label><input name="person_in_charge" class="form-control" value="{{old('person_in_charge')}}"></div>
  <div class="col-md-4"><label class="form-label">Nama pengawas</label><input name="supervisor" class="form-control" value="{{old('supervisor')}}"></div>
  <div class="col-md-4"><label class="form-label">Mutu beton rencana <span class="text-danger">*</span></label><input name="concrete_grade" class="form-control" value="{{old('concrete_grade')}}" placeholder="Contoh: f'c 25 MPa" required></div>
  <div class="col-md-4"><label class="form-label">Jenis konstruksi <span class="text-danger">*</span></label><input name="construction_type" class="form-control" value="{{old('construction_type')}}" placeholder="Contoh: gedung, jalan, jembatan" required></div>
  <div class="col-md-6"><label class="form-label">Kondisi lingkungan <span class="text-danger">*</span></label><input name="environment" class="form-control" value="{{old('environment')}}" required></div>

  <div class="col-12"><h5 class="section-title">C. Kebutuhan Pengujian</h5></div>
  <div class="col-md-6"><label class="form-label">Jenis paket <span class="text-danger">*</span></label><select name="service_type" class="form-select" required><option value="">Pilih paket</option>@foreach($services as $key=>$label)<option value="{{$key}}" @selected(old('service_type')===$key)>{{$label}}</option>@endforeach</select></div>
  <div class="col-md-3"><label class="form-label">Tanggal diharapkan <span class="text-danger">*</span></label><input type="date" name="requested_date" min="{{date('Y-m-d')}}" class="form-control" value="{{old('requested_date')}}" required></div>
  <div class="col-md-3"><label class="form-label">Jumlah sampel <span class="text-danger">*</span></label><input type="number" name="sample_quantity" min="1" class="form-control" value="{{old('sample_quantity',1)}}" required></div>
  <div class="col-12"><label class="form-label">Uraian sampel/material <span class="text-danger">*</span></label><input name="sample_description" class="form-control" value="{{old('sample_description')}}" placeholder="Contoh: pasir, kerikil, semen, air, atau silinder beton" required></div>
  <div class="col-12"><label class="form-label">Catatan dan kebutuhan khusus</label><textarea name="description" class="form-control" rows="4">{{old('description')}}</textarea></div>

  <div class="col-12"><h5 class="section-title">D. Surat Permohonan</h5></div>
  <div class="col-12"><div class="upload-box"><label class="form-label"><i class="bi bi-paperclip me-2"></i>Unggah surat permohonan resmi <span class="text-danger">*</span></label><input type="file" name="application_letter" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required><div class="form-text">Format PDF, JPG, atau PNG. Ukuran maksimum 10 MB.</div></div></div>
  <div class="col-12 d-flex justify-content-end mt-4"><button class="btn btn-success btn-lg px-5"><i class="bi bi-send me-2"></i>Kirim ke Laboratorium</button></div>
 </form>
</div>

@php
$statusLabels=['diajukan'=>'Diajukan','ditinjau'=>'Sedang ditinjau','perlu-perbaikan'=>'Perlu diperbaiki','diterima'=>'Diterima','dijadwalkan'=>'Dijadwalkan','selesai'=>'Selesai','ditolak'=>'Ditolak'];
$statusColors=['diajukan'=>'secondary','ditinjau'=>'info','perlu-perbaikan'=>'warning','diterima'=>'success','dijadwalkan'=>'primary','selesai'=>'success','ditolak'=>'danger'];
@endphp
<h4 class="fw-bold mb-3">Permohonan yang Sudah Dikirim</h4>
@forelse($requests as $item)
<div class="card p-4 mb-3 border-start border-4 border-{{$statusColors[$item->status]??'secondary'}}"><div class="d-flex justify-content-between gap-3"><div><div class="text-secondary small">{{$item->request_number}} • {{$item->created_at->format('d/m/Y H:i')}}</div><h5 class="fw-bold my-1">{{$item->project_number}} — {{$item->work_name}}</h5><div>{{$services[$item->service_type]??$item->service_type}}</div></div><span class="badge text-bg-{{$statusColors[$item->status]??'secondary'}} align-self-start">{{$statusLabels[$item->status]??ucfirst($item->status)}}</span></div><hr><div class="row small g-2"><div class="col-md-4"><b>Pemilik:</b> {{$item->owner}}</div><div class="col-md-4"><b>Mutu:</b> {{$item->concrete_grade}}</div><div class="col-md-4"><b>Sampel:</b> {{$item->sample_description}} ({{$item->sample_quantity}})</div><div class="col-12"><b>Lokasi:</b> {{$item->project_location}}</div></div>@if($item->application_letter_path)<a class="btn btn-sm btn-outline-secondary mt-3 align-self-start" target="_blank" href="{{asset('storage/'.$item->application_letter_path)}}"><i class="bi bi-file-earmark-pdf me-1"></i>Lihat Surat Permohonan</a>@endif @if($item->project)<div class="alert alert-success mt-3 mb-0"><b>Data Proyek telah dibuat:</b> {{$item->project->number}} — {{$item->project->name}}</div>@endif @if($item->admin_notes)<div class="alert alert-{{$item->status==='ditolak'?'danger':'info'}} mt-3 mb-0"><b>Keterangan laboratorium:</b><br>{{$item->admin_notes}}</div>@endif</div>
@empty <div class="card p-5 text-center text-secondary">Belum ada permohonan yang dikirim.</div>@endforelse
</main>
<footer class="footer"><div class="container d-flex justify-content-between flex-wrap gap-2"><div>Laboratorium Beton • Universitas Muhammadiyah Buton</div><small>Dikembangkan oleh MUHAMMAD ABDU, S.T., M.T.</small></div></footer>
</body>
</html>
