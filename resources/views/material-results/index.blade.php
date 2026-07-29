@extends('layouts.app')
@section('title','Hasil Pengujian')
@section('subtitle','Arsip berdasarkan proyek')
@section('content')
<div class="mb-4"><h3 class="fw-bold">Daftar Proyek dan Hasil Pemeriksaan</h3><p class="text-secondary">Setiap proyek memuat pemeriksaan seluruh material, desain campuran, lalu kuat tekan.</p></div>
<div class="card overflow-hidden"><table class="table table-hover align-middle mb-0"><thead><tr><th>Nomor</th><th>Nama Proyek</th><th>Mutu Beton</th><th>Hasil Tersimpan</th><th></th></tr></thead><tbody>
@forelse($projects as $p)<tr><td><b>{{$p->number}}</b></td><td>{{$p->name}}<div class="small text-secondary">{{$p->location}}</div></td><td>{{$p->concrete_grade??'—'}}</td><td><span class="badge badge-soft">{{$p->aggregate_test_runs_count+$p->laboratory_workflows_count}} pengujian/perhitungan</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{route('material-results.project',$p)}}">Buka Hasil <i class="bi bi-arrow-right ms-1"></i></a></td></tr>
@empty<tr><td colspan="5" class="text-center py-5 text-secondary">Belum ada proyek.</td></tr>@endforelse
</tbody></table></div>
@endsection
