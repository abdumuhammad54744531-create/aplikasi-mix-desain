@extends('layouts.app')
@section('title','Data Proyek')
@section('subtitle','Identitas pekerjaan, kontrak, lokasi, dan peta')
@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>.project-map{height:310px;border-radius:10px;border:1px solid #ccd7db}.project-form-section{border-top:1px solid #e2e8ea;padding-top:18px;margin-top:18px}.project-form-section:first-child{border-top:0;padding-top:0;margin-top:0}</style>
@endpush
@section('content')
<div class="d-flex justify-content-between mb-3"><div><h3 class="fw-bold">Daftar Proyek</h3><p class="text-secondary">Data pada form ini menjadi sumber utama BAB 1.1 dan lokasi laporan.</p></div>@if(auth()->user()->hasPermission('projects.create'))<button class="btn btn-primary align-self-start" data-bs-toggle="modal" data-bs-target="#add"><i class="bi bi-plus-lg me-2"></i>Tambah Proyek</button>@endif</div>
<div class="card p-3"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Proyek/Paket</th><th>Pemilik</th><th>Lokasi</th><th>Kontrak</th><th>Mutu/Konstruksi</th><th>Aksi</th></tr></thead><tbody>
@forelse($projects as $p)<tr><td><b>{{$p->name}}</b><div class="small text-secondary">{{$p->number}}</div><div>{{$p->work_package?:'-'}}</div></td><td>{{$p->owner?:'-'}}</td><td>{{$p->location_address?:($p->location?:'-')}}@if($p->latitude!==null)<div class="small text-secondary">{{$p->latitude}}, {{$p->longitude}}</div>@endif</td><td>{{$p->contract_number?:'-'}}<div class="small text-secondary">{{$p->contract_date?->format('d/m/Y')?:'-'}}</div></td><td>{{$p->concrete_grade?:'-'}}<div class="small text-secondary">{{$p->construction_type?:'-'}}</div></td><td><div class="d-flex gap-1">@if(auth()->user()->hasPermission('projects.edit'))<button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProject{{$p->id}}" title="Ubah proyek"><i class="bi bi-pencil-square"></i></button>@endif @if(auth()->user()->hasPermission('projects.delete'))<form method="post" action="{{route('projects.destroy',$p)}}" onsubmit="return confirm('Arsipkan proyek ini?')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger" title="Arsipkan"><i class="bi bi-trash"></i></button></form>@endif</div></td></tr>
@empty<tr><td colspan="6" class="text-center text-secondary py-5">Belum ada proyek.</td></tr>@endforelse
</tbody></table></div></div>

@if(auth()->user()->hasPermission('projects.create'))
<div class="modal fade" id="add"><div class="modal-dialog modal-xl"><form method="post" action="{{route('projects.store')}}" enctype="multipart/form-data" class="modal-content project-form">@csrf
<div class="modal-header"><h5 class="modal-title">Proyek Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">@include('projects.partials.form',['project'=>null,'mapId'=>'map-add'])</div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Proyek</button></div></form></div></div>
@endif

@foreach($projects as $p) @if(auth()->user()->hasPermission('projects.edit'))
<div class="modal fade" id="editProject{{$p->id}}"><div class="modal-dialog modal-xl"><form method="post" action="{{route('projects.update',$p)}}" enctype="multipart/form-data" class="modal-content project-form">@csrf @method('put')
<div class="modal-header"><h5 class="modal-title">Ubah Proyek — {{$p->name}}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">@include('projects.partials.form',['project'=>$p,'mapId'=>'map-'.$p->id])</div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan Perubahan</button></div></form></div></div>
@endif @endforeach
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.querySelectorAll('.project-form').forEach(form=>{
 const mapEl=form.querySelector('.project-map'),lat=form.querySelector('[name="latitude"]'),lng=form.querySelector('[name="longitude"]');let map,marker;
 const init=()=>{if(map)return;const initial=[Number(lat.value)||-5.4667,Number(lng.value)||122.6333];map=L.map(mapEl).setView(initial,lat.value&&lng.value?15:11);L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'© OpenStreetMap'}).addTo(map);marker=L.marker(initial,{draggable:true}).addTo(map);const set=point=>{lat.value=Number(point.lat).toFixed(7);lng.value=Number(point.lng).toFixed(7);marker.setLatLng(point)};map.on('click',e=>set(e.latlng));marker.on('dragend',()=>set(marker.getLatLng()));setTimeout(()=>map.invalidateSize(),150)};
 form.closest('.modal')?.addEventListener('shown.bs.modal',init);[lat,lng].forEach(input=>input.addEventListener('change',()=>{if(!map)init();const point=[Number(lat.value),Number(lng.value)];if(point.every(Number.isFinite)){marker.setLatLng(point);map.panTo(point)}}));
});
</script>
@php($requestedEditProject=$projects->firstWhere('id',(int)request('edit')))
@if($requestedEditProject)<script>document.addEventListener('DOMContentLoaded',()=>new bootstrap.Modal(document.getElementById('editProject{{$requestedEditProject->id}}')).show());</script>@endif
@endpush
