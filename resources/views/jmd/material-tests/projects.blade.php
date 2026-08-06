@extends('layouts.app')
@section('title','Pengujian Material JMD')
@section('subtitle','Pilih proyek untuk membuka lembar kerja pengujian')
@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div><h3 class="fw-bold mb-1">Proyek JMD</h3><p class="text-secondary mb-0">Setiap proyek memiliki data dan riwayat pengujian yang terisolasi.</p></div>
    <a href="{{ route('projects.index') }}" class="btn btn-outline-primary"><i class="bi bi-plus-lg me-2"></i>Kelola Proyek</a>
</div>
<div class="card p-3"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Nomor</th><th>Proyek</th><th>Pemilik</th><th>Status JMD</th><th></th></tr></thead>
    <tbody>@forelse($projects as $project)<tr>
        <td>{{ $project->jmd_number ?: $project->number }}</td>
        <td><b>{{ $project->name }}</b><div class="small text-secondary">{{ $project->location ?: 'Lokasi belum diisi' }}</div></td>
        <td>{{ $project->owner ?: '—' }}</td>
        <td><span class="badge badge-soft">{{ $project->jmd_status?->label() ?? 'Draft' }}</span></td>
        <td class="text-end"><a href="{{ route('jmd.material-tests.index',$project) }}" class="btn btn-sm btn-primary">Buka Pengujian <i class="bi bi-arrow-right ms-1"></i></a></td>
    </tr>@empty<tr><td colspan="5" class="text-center text-secondary py-5">Belum ada proyek.</td></tr>@endforelse</tbody>
</table></div></div>
<div class="mt-3">{{ $projects->links() }}</div>
@endsection
