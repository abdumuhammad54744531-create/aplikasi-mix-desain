@extends('layouts.app')
@section('title','Permohonan Pengujian Laboratorium')
@section('subtitle',$isApplicant?'Lengkapi data proyek dan kebutuhan pengujian':'Periksa permohonan, setujui, lalu lanjutkan sebagai proyek')
@section('content')
@php
$statusLabels=['diajukan'=>'Diajukan','ditinjau'=>'Sedang ditinjau','perlu-perbaikan'=>'Perlu diperbaiki','diterima'=>'Diterima','dijadwalkan'=>'Dijadwalkan','selesai'=>'Selesai','ditolak'=>'Ditolak'];
$statusColors=['diajukan'=>'secondary','ditinjau'=>'info','perlu-perbaikan'=>'warning','diterima'=>'success','dijadwalkan'=>'primary','selesai'=>'success','ditolak'=>'danger'];
$showDate=fn($date)=>$date?->format('d/m/Y')?:'—';
@endphp

@if(session('info'))<div class="alert alert-info">{{session('info')}}</div>@endif

@if($isApplicant)
<div class="card p-4 mb-4">
 <div class="d-flex justify-content-between align-items-start gap-3 mb-3"><div><h3 class="fw-bold mb-1">Formulir Permohonan Pengujian</h3><p class="text-secondary mb-0">Data proyek di bawah akan langsung menjadi Data Proyek setelah disetujui laboratorium. Pastikan seluruh identitas benar.</p></div><span class="badge badge-soft">Diisi oleh pemohon</span></div>
 <form method="post" action="{{route('lab-requests.store')}}" class="row g-3">@csrf
  <div class="col-12"><h5 class="fw-bold border-bottom pb-2 mt-2">A. Identitas Pemohon</h5></div>
  <div class="col-md-4"><label class="form-label">Nama pemohon</label><input class="form-control" value="{{auth()->user()->name}}" disabled></div>
  <div class="col-md-4"><label class="form-label">Instansi/Perusahaan</label><input name="institution" class="form-control" value="{{old('institution',auth()->user()->institution)}}"></div>
  <div class="col-md-4"><label class="form-label">Nomor telepon/WhatsApp</label><input name="phone" class="form-control" value="{{old('phone')}}" required></div>

  <div class="col-12"><h5 class="fw-bold border-bottom pb-2 mt-3">B. Data Lengkap Proyek</h5></div>
  <div class="col-md-4"><label class="form-label">Nomor proyek</label><input name="project_number" class="form-control" value="{{old('project_number')}}" required></div>
  <div class="col-md-8"><label class="form-label">Nama proyek/pekerjaan</label><input name="work_name" class="form-control" value="{{old('work_name')}}" required></div>
  <div class="col-md-6"><label class="form-label">Paket pekerjaan</label><input name="work_package" class="form-control" value="{{old('work_package')}}" required></div>
  <div class="col-md-6"><label class="form-label">Pemilik pekerjaan</label><input name="owner" class="form-control" value="{{old('owner')}}" required></div>
  <div class="col-md-6"><label class="form-label">Kontraktor pelaksana</label><input name="contractor" class="form-control" value="{{old('contractor')}}"></div>
  <div class="col-md-6"><label class="form-label">Konsultan</label><input name="consultant" class="form-control" value="{{old('consultant')}}"></div>
  <div class="col-12"><label class="form-label">Lokasi proyek</label><textarea name="project_location" class="form-control" rows="2" required>{{old('project_location')}}</textarea></div>
  <div class="col-md-6"><label class="form-label">Nomor kontrak</label><input name="contract_number" class="form-control" value="{{old('contract_number')}}"></div>
  <div class="col-md-6"><label class="form-label">Tanggal kontrak</label><input type="date" name="contract_date" class="form-control" value="{{old('contract_date')}}"></div>
  <div class="col-md-4"><label class="form-label">Tanggal mulai</label><input type="date" name="start_date" class="form-control" value="{{old('start_date')}}"></div>
  <div class="col-md-4"><label class="form-label">Tanggal selesai</label><input type="date" name="end_date" class="form-control" value="{{old('end_date')}}"></div>
  <div class="col-md-4"><label class="form-label">Penanggung jawab proyek</label><input name="person_in_charge" class="form-control" value="{{old('person_in_charge')}}" required></div>
  <div class="col-md-4"><label class="form-label">Nama pengawas</label><input name="supervisor" class="form-control" value="{{old('supervisor')}}"></div>
  <div class="col-md-4"><label class="form-label">Mutu beton rencana</label><input name="concrete_grade" class="form-control" value="{{old('concrete_grade')}}" placeholder="Contoh: f'c 25 MPa" required></div>
  <div class="col-md-4"><label class="form-label">Jenis konstruksi</label><input name="construction_type" class="form-control" value="{{old('construction_type')}}" placeholder="Contoh: gedung, jalan, jembatan" required></div>
  <div class="col-md-6"><label class="form-label">Kondisi lingkungan</label><input name="environment" class="form-control" value="{{old('environment')}}"></div>

  <div class="col-12"><h5 class="fw-bold border-bottom pb-2 mt-3">C. Kebutuhan Pengujian Laboratorium</h5></div>
  <div class="col-md-6"><label class="form-label">Jenis layanan</label><select name="service_type" class="form-select" required><option value="">Pilih layanan</option>@foreach($services as $key=>$label)<option value="{{$key}}" @selected(old('service_type')===$key)>{{$label}}</option>@endforeach</select></div>
  <div class="col-md-3"><label class="form-label">Tanggal diharapkan</label><input type="date" name="requested_date" min="{{date('Y-m-d')}}" class="form-control" value="{{old('requested_date')}}"></div>
  <div class="col-md-3"><label class="form-label">Jumlah sampel</label><input type="number" name="sample_quantity" min="1" class="form-control" value="{{old('sample_quantity',1)}}" required></div>
  <div class="col-12"><label class="form-label">Uraian sampel/material</label><input name="sample_description" class="form-control" value="{{old('sample_description')}}" placeholder="Contoh: pasir, kerikil, semen, air, atau silinder beton" required></div>
  <div class="col-12"><label class="form-label">Catatan dan kebutuhan khusus</label><textarea name="description" class="form-control" rows="4" placeholder="Jelaskan lingkup pengujian, target mutu, atau informasi tambahan.">{{old('description')}}</textarea></div>
  <div class="col-12 d-flex justify-content-end"><button class="btn btn-primary px-5"><i class="bi bi-send me-2"></i>Kirim Permohonan Lengkap</button></div>
 </form>
</div>

<h4 class="fw-bold mb-3">Riwayat Permohonan Saya</h4>
@forelse($requests as $item)
<div class="card p-4 mb-3 border-start border-4 border-{{$statusColors[$item->status]??'secondary'}}">
 <div class="d-flex justify-content-between gap-3"><div><div class="text-secondary small">{{$item->request_number}} • {{$item->created_at->format('d/m/Y H:i')}}</div><h5 class="fw-bold my-1">{{$item->project_number}} — {{$item->work_name}}</h5><div>{{$services[$item->service_type]??$item->service_type}}</div></div><span class="badge text-bg-{{$statusColors[$item->status]??'secondary'}} align-self-start">{{$statusLabels[$item->status]??ucfirst($item->status)}}</span></div>
 <hr><div class="row small g-2"><div class="col-md-4"><b>Pemilik:</b> {{$item->owner}}</div><div class="col-md-4"><b>Mutu:</b> {{$item->concrete_grade}}</div><div class="col-md-4"><b>Sampel:</b> {{$item->sample_description}} ({{$item->sample_quantity}})</div><div class="col-12"><b>Lokasi:</b> {{$item->project_location}}</div></div>
 @if($item->project)<div class="alert alert-success mt-3 mb-0"><b>Data Proyek telah dibuat:</b> {{$item->project->number}} — {{$item->project->name}}</div>@endif
 @if($item->admin_notes)<div class="alert alert-{{$item->status==='ditolak'?'danger':'info'}} mt-3 mb-0"><b>Keterangan laboratorium:</b><br>{{$item->admin_notes}}</div>@endif
</div>
@empty <div class="card p-5 text-center text-secondary"><i class="bi bi-inbox fs-1"></i><div class="mt-2">Belum ada permohonan yang dikirim.</div></div>@endforelse

@else
<div class="d-flex justify-content-between align-items-start mb-4"><div><h3 class="fw-bold mb-1">Daftar Permohonan Pengujian</h3><p class="text-secondary mb-0">Pemohon telah mengisi seluruh data proyek. Periksa, setujui, lalu lanjutkan ke Data Proyek.</p></div><span class="badge badge-soft fs-6">{{$requests->count()}} permohonan</span></div>
@forelse($requests as $item)
<div class="card p-4 mb-4">
 <div class="d-flex justify-content-between gap-3"><div><div class="text-secondary small">{{$item->request_number}} • {{$item->created_at->format('d/m/Y H:i')}}</div><h4 class="fw-bold my-1">{{$item->project_number}} — {{$item->work_name}}</h4><div><b>{{$item->applicant_name}}</b> — {{$item->institution?:'Perorangan'}} • {{$item->phone}}</div></div><span class="badge text-bg-{{$statusColors[$item->status]??'secondary'}} align-self-start">{{$statusLabels[$item->status]??ucfirst($item->status)}}</span></div>
 @if($item->application_letter_path)<div class="mt-3"><a href="{{asset('storage/'.$item->application_letter_path)}}" target="_blank" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf me-2"></i>Buka Surat Permohonan</a></div>@endif

 <h6 class="fw-bold mt-4">Data Proyek dari Pemohon</h6>
 <div class="table-responsive"><table class="table table-bordered table-sm mb-3"><tbody>
  <tr><th style="width:18%">Paket pekerjaan</th><td>{{$item->work_package}}</td><th style="width:18%">Pemilik</th><td>{{$item->owner}}</td></tr>
  <tr><th>Kontraktor</th><td>{{$item->contractor?:'—'}}</td><th>Konsultan</th><td>{{$item->consultant?:'—'}}</td></tr>
  <tr><th>Lokasi proyek</th><td colspan="3">{{$item->project_location}}</td></tr>
  <tr><th>Nomor kontrak</th><td>{{$item->contract_number?:'—'}}</td><th>Tanggal kontrak</th><td>{{$showDate($item->contract_date)}}</td></tr>
  <tr><th>Masa pekerjaan</th><td>{{$showDate($item->start_date)}} s.d. {{$showDate($item->end_date)}}</td><th>Penanggung jawab</th><td>{{$item->person_in_charge}}</td></tr>
  <tr><th>Pengawas</th><td>{{$item->supervisor?:'—'}}</td><th>Mutu beton</th><td>{{$item->concrete_grade}}</td></tr>
  <tr><th>Jenis konstruksi</th><td>{{$item->construction_type}}</td><th>Lingkungan</th><td>{{$item->environment?:'—'}}</td></tr>
 </tbody></table></div>

 <h6 class="fw-bold">Data Permohonan Pengujian</h6>
 <div class="row g-3"><div class="col-md-3"><div class="small text-secondary">Jenis layanan</div><b>{{$services[$item->service_type]??$item->service_type}}</b></div><div class="col-md-3"><div class="small text-secondary">Sampel</div><b>{{$item->sample_description}} ({{$item->sample_quantity}})</b></div><div class="col-md-3"><div class="small text-secondary">Tanggal diharapkan</div><b>{{$showDate($item->requested_date)}}</b></div><div class="col-md-3"><div class="small text-secondary">Catatan</div>{{$item->description?:'—'}}</div></div>

 <div class="row g-2 align-items-end border-top mt-3 pt-3">
  <div class="col-lg-7"><form method="post" action="{{route('lab-requests.status',$item)}}" class="row g-2 align-items-end">@csrf @method('patch')<div class="col-md-4"><label class="form-label">Status pemeriksaan</label><select name="status" class="form-select">@foreach($statusLabels as $key=>$label)<option value="{{$key}}" @selected($item->status===$key)>{{$label}}</option>@endforeach</select></div><div class="col-md-6"><label class="form-label">Keterangan untuk pemohon</label><input name="admin_notes" class="form-control" value="{{$item->admin_notes}}" placeholder="Kekurangan data atau keterangan"></div><div class="col-md-2"><button class="btn btn-outline-primary w-100">Simpan</button></div></form></div>
  <div class="col-lg-5 text-lg-end">
   @if($item->project)
    <a href="{{route('projects.index',['edit'=>$item->project_id])}}" class="btn btn-success"><i class="bi bi-pencil-square me-2"></i>Buka dan Edit Data Proyek</a>
   @else
    <form method="post" action="{{route('lab-requests.approve-project',$item)}}" class="d-inline" onsubmit="return confirm('Setujui permohonan dan buat Data Proyek secara otomatis?')">@csrf<button class="btn btn-success px-4"><i class="bi bi-check2-circle me-2"></i>Setujui dan Lanjutkan</button></form>
   @endif
  </div>
 </div>
</div>
@empty <div class="card p-5 text-center text-secondary">Belum ada permohonan pengujian laboratorium.</div>@endforelse
@endif
@endsection
