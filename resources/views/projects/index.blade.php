@extends('layouts.app')
@section('title','Data Proyek')
@section('subtitle','Identitas utama pekerjaan')
@section('content')
@php $projectFields=[['name','Nama proyek',1],['owner','Pemilik / perusahaan pemohon',0],['location','Lokasi proyek',0],['contract_number','Nomor kontrak',0],['construction_type','Jenis konstruksi',0]]; @endphp
<div class="d-flex justify-content-between mb-3"><div><h3 class="fw-bold">Daftar Proyek</h3><p class="text-secondary">Isi hanya identitas utama proyek. Nomor internal dibuat otomatis oleh sistem.</p></div><button class="btn btn-primary align-self-start" data-bs-toggle="modal" data-bs-target="#add"><i class="bi bi-plus-lg me-2"></i>Tambah Proyek</button></div>
<div class="card p-3"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Nama proyek</th><th>Pemilik / pemohon</th><th>Lokasi</th><th>Nomor kontrak</th><th>Jenis konstruksi</th><th>Aksi</th></tr></thead><tbody>
@forelse($projects as $p)
<tr><td><b>{{$p->name}}</b><div class="small text-secondary">{{$p->number}}</div></td><td>{{$p->owner??'—'}}</td><td>{{$p->location??'—'}}</td><td>{{$p->contract_number??'—'}}</td><td>{{$p->construction_type??'—'}}</td><td><div class="d-flex gap-1"><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProject{{$p->id}}" title="Ubah proyek" aria-label="Ubah proyek"><i class="bi bi-pencil-square"></i></button><form method="post" action="{{route('projects.destroy',$p)}}" onsubmit="return confirm('Arsipkan proyek ini?')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger" title="Arsipkan" aria-label="Arsipkan proyek"><i class="bi bi-trash"></i></button></form></div></td></tr>
@empty<tr><td colspan="6" class="text-center text-secondary py-5">Belum ada proyek. Tambahkan proyek pertama.</td></tr>@endforelse
</tbody></table></div></div>

<div class="modal fade" id="add"><div class="modal-dialog modal-lg"><form method="post" action="{{route('projects.store')}}" class="modal-content">@csrf
<div class="modal-header"><h5 class="modal-title">Proyek Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3">
@foreach($projectFields as [$field,$label,$required])<div class="{{$field==='name'?'col-12':'col-md-6'}}"><label class="form-label">{{$label}}{{$required?' *':''}}</label>@if($field==='location')<textarea class="form-control" name="{{$field}}" rows="2" {{$required?'required':''}}></textarea>@else<input class="form-control" name="{{$field}}" {{$required?'required':''}}>@endif</div>@endforeach
</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Proyek</button></div></form></div></div>

@foreach($projects as $p)
<div class="modal fade" id="editProject{{$p->id}}"><div class="modal-dialog modal-lg"><form method="post" action="{{route('projects.update',$p)}}" class="modal-content">@csrf @method('put')
<div class="modal-header"><h5 class="modal-title">Ubah Proyek — {{$p->name}}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><div class="row g-3">
@foreach($projectFields as [$field,$label,$required])<div class="{{$field==='name'?'col-12':'col-md-6'}}"><label class="form-label">{{$label}}{{$required?' *':''}}</label>@if($field==='location')<textarea class="form-control" name="{{$field}}" rows="2" {{$required?'required':''}}>{{$p->$field}}</textarea>@else<input class="form-control" name="{{$field}}" value="{{$p->$field}}" {{$required?'required':''}}>@endif</div>@endforeach
</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan Perubahan</button></div></form></div></div>
@endforeach
@endsection
@php($requestedEditProject=$projects->firstWhere('id',(int)request('edit')))
@if($requestedEditProject)
@push('scripts')
<script>document.addEventListener('DOMContentLoaded',()=>new bootstrap.Modal(document.getElementById('editProject{{$requestedEditProject->id}}')).show());</script>
@endpush
@endif
