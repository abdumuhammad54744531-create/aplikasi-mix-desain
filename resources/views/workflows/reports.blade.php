@extends('layouts.app')
@section('title','Laporan')
@section('subtitle','Satu berkas laporan untuk setiap proyek dan perusahaan')
@section('content')
<div class="d-flex justify-content-between align-items-start mb-4"><div><h3 class="fw-bold">Berkas Laporan Proyek</h3><p class="text-secondary">Setiap kartu merupakan satu proyek. Buka kartu untuk melihat seluruh pemeriksaan material, desain campuran, dan kuat tekan.</p></div></div>
<div class="row g-4">
@forelse($projects as $p)
@php $badge=match($p->report_status){'disetujui'=>'text-bg-success','diperiksa'=>'text-bg-warning','draft'=>'text-bg-secondary',default=>'text-bg-light'}; @endphp
<div class="col-md-6 col-xl-4"><a href="{{route('workflow.report.project',$p)}}" class="text-decoration-none text-reset"><div class="card p-4 h-100 border-start border-4 {{$p->report_status==='disetujui'?'border-success':($p->report_status==='diperiksa'?'border-warning':'border-secondary')}}"><div class="d-flex justify-content-between gap-2"><div><div class="small text-secondary">{{$p->number}}</div><h5 class="fw-bold mt-1">{{$p->name}}</h5></div><span class="badge {{$badge}} align-self-start">{{$p->report_status==='draft'?'Draf':ucfirst($p->report_status)}}</span></div><hr><div class="small"><div><i class="bi bi-building me-2"></i>{{$p->owner ?: 'Perusahaan belum diisi'}}</div><div class="mt-2"><i class="bi bi-geo-alt me-2"></i>{{$p->location ?: 'Lokasi belum diisi'}}</div><div class="mt-2"><i class="bi bi-folder2-open me-2"></i>{{$p->report_count}} data dalam satu berkas</div></div><div class="mt-3 text-primary fw-semibold">Buka berkas proyek <i class="bi bi-arrow-right ms-1"></i></div></div></a></div>
@empty<div class="col-12"><div class="card p-5 text-center text-secondary">Belum ada proyek.</div></div>@endforelse
</div>
@endsection
