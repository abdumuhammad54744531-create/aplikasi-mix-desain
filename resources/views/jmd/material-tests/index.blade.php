@extends('layouts.app')
@section('title','Pengujian Material JMD')
@section('subtitle',($project->jmd_number ?: $project->number).' — '.$project->name)
@section('content')
<div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
    <div><a class="small text-decoration-none" href="{{ route('jmd.material-tests.projects') }}"><i class="bi bi-arrow-left me-1"></i>Pilih proyek lain</a><h3 class="fw-bold mt-2 mb-1">{{ $project->name }}</h3><div class="text-secondary">{{ $project->owner ?: 'Pemilik belum diisi' }} · {{ $project->location ?: 'Lokasi belum diisi' }}</div></div>
    <div class="text-end"><span class="badge badge-soft">{{ $project->jmd_status?->label() ?? 'Draft' }}</span>@if($project->locked_at)<div class="small text-danger mt-2"><i class="bi bi-lock-fill"></i> Proyek dikunci</div>@endif</div>
</div>
<div class="row g-3">
@foreach($modules as $module)
<div class="col-md-6 col-xl-4"><div class="card h-100 p-3">
    <div class="d-flex gap-3"><div class="metric-icon"><i class="bi bi-{{ $module['icon'] }}"></i></div><div class="flex-grow-1"><h5 class="mb-1">{{ $module['title'] }}</h5><div class="small text-secondary">{{ $module['count'] }} pengujian tersimpan</div></div></div>
    @if($module['latest'])<div class="border-top mt-3 pt-3 small"><div class="d-flex justify-content-between"><span>{{ $module['latest']->test_number }}</span><span class="badge {{ $module['latest']->status==='completed'?'text-bg-success':'text-bg-warning' }}">{{ str_replace('_',' ',$module['latest']->status) }}</span></div><div class="text-secondary mt-1">{{ optional($module['latest']->tested_at)->format('d/m/Y') }} · {{ $module['latest']->technician }}</div></div>@else<div class="border-top mt-3 pt-3 small text-secondary">Belum ada hasil.</div>@endif
    <div class="mt-auto pt-3 d-flex gap-2"><a href="{{ route('jmd.material-tests.form',[$project,$module['key']]) }}" class="btn btn-sm btn-primary flex-grow-1">Buka lembar kerja</a>@if($module['latest'])<a href="{{ route('jmd.material-tests.form',[$project,$module['key'],'test'=>$module['latest']->id]) }}" class="btn btn-sm btn-outline-secondary" title="Ubah hasil terakhir"><i class="bi bi-pencil"></i></a>@endif</div>
</div></div>
@endforeach
</div>
@endsection
