@extends('layouts.app')
@section('title','Dokumentasi Pemeriksaan')
@section('subtitle','Foto ditautkan ke proyek dan jenis pemeriksaan, lalu otomatis masuk laporan akhir')
@section('content')
<div class="card p-4 mb-4">
 <form method="get" class="row g-3 align-items-end">
  <div class="col-md-6"><label class="form-label">Proyek</label><select name="project" class="form-select" onchange="this.form.submit()">@foreach($projects as $project)<option value="{{$project->id}}" @selected($selectedProject==$project->id)>{{$project->number}} — {{$project->name}}</option>@endforeach</select></div>
  <div class="col-md-4"><label class="form-label">Pemeriksaan</label><select name="module" class="form-select" onchange="this.form.submit()">@foreach($modules as $key=>$label)<option value="{{$key}}" @selected($selectedModule===$key)>{{$label}}</option>@endforeach</select></div>
 </form>
</div>
@if($selectedProject)
<div class="card p-4 mb-4">
 <h5 class="fw-bold mb-3">Tambah Dokumentasi — {{$modules[$selectedModule]}}</h5>
 <form method="post" action="{{route('documentation.store')}}" enctype="multipart/form-data" class="row g-3">@csrf
  <input type="hidden" name="project_id" value="{{$selectedProject}}"><input type="hidden" name="module" value="{{$selectedModule}}">
  <div class="col-md-5"><label class="form-label">Judul kegiatan *</label><input name="title" class="form-control" required placeholder="Contoh: Pengujian kadar air pasir"></div>
  <div class="col-md-3"><label class="form-label">Tanggal</label><input type="date" name="documented_at" class="form-control" value="{{date('Y-m-d')}}"></div>
  <div class="col-md-4"><label class="form-label">Foto (boleh banyak) *</label><input type="file" name="photos[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple required></div>
  <div class="col-12"><label class="form-label">Keterangan</label><textarea name="description" class="form-control" rows="2" placeholder="Lokasi, tahap pengujian, atau keterangan foto"></textarea></div>
  <div class="col-12"><button class="btn btn-primary"><i class="bi bi-cloud-arrow-up me-2"></i>Simpan Dokumentasi</button></div>
 </form>
</div>
<div class="row g-3">
 @forelse($documents as $document)
 <div class="col-md-4"><div class="card h-100 overflow-hidden"><img src="{{asset('storage/'.$document->photo_path)}}" alt="{{$document->title}}" style="height:230px;object-fit:cover"><div class="p-3"><b>{{$document->title}}</b><div class="small text-secondary">{{$document->documented_at?->format('d/m/Y')}}</div><p class="small mb-2">{{$document->description}}</p><form method="post" action="{{route('documentation.destroy',$document)}}" onsubmit="return confirm('Pindahkan foto ini ke Arsip?')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus</button></form></div></div></div>
 @empty <div class="col-12"><div class="card p-5 text-center text-secondary">Belum ada foto pada pemeriksaan ini.</div></div>@endforelse
</div>
@endif
@endsection
