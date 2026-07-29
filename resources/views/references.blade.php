@extends('layouts.app') @section('title','Tabel Referensi') @section('subtitle','Katalog standar dan sumber parameter resmi') @section('content')
<div class="d-flex justify-content-between align-items-start mb-4"><div><h3 class="fw-bold">Standar Laboratorium Beton</h3><p class="text-secondary mb-0">Metadata standar diverifikasi dari katalog BSN. Isi tabel teknis tetap diinput dari dokumen resmi laboratorium.</p></div><span class="badge badge-soft fs-6">{{$total}} standar</span></div>

<div class="card p-3 mb-4"><form class="row g-2" method="get"><div class="col-md-7"><div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input class="form-control" name="q" value="{{request('q')}}" placeholder="Cari nomor atau judul standar"></div></div>
<div class="col-md-3"><select class="form-select" name="category"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{$category}}" @selected(request('category')===$category)>{{$category}}</option>@endforeach</select></div>
<div class="col-md-2 d-grid"><button class="btn btn-primary">Tampilkan</button></div></form></div>

<div class="card overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Standar</th><th>Judul</th><th>Kategori</th><th>Status</th><th>Data teknis</th><th></th></tr></thead><tbody>
@forelse($standards as $standard)<tr><td><b class="text-nowrap">{{$standard->standard_number}}</b><div class="small text-secondary">Tahun {{$standard->standard_year}}</div></td><td style="min-width:320px">{{$standard->name}}</td><td><span class="badge text-bg-light">{{$standard->category}}</span></td><td><span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>{{$standard->status}}</span></td><td><span class="text-warning small"><i class="bi bi-exclamation-circle me-1"></i>Belum diisi</span></td><td>@if($standard->catalog_url)<a class="btn btn-sm btn-outline-secondary" href="{{$standard->catalog_url}}" target="_blank" rel="noopener" title="Buka katalog BSN"><i class="bi bi-box-arrow-up-right"></i></a>@endif</td></tr>
@empty<tr><td colspan="6" class="text-center text-secondary py-5"><i class="bi bi-search fs-2"></i><p class="mt-2 mb-0">Standar tidak ditemukan.</p></td></tr>@endforelse
</tbody></table></div></div>
<div class="alert alert-info mt-4 mb-0"><i class="bi bi-shield-check me-2"></i>Daftar ini hanya menyimpan metadata. Nilai batas, tabel campuran, dan parameter lain tidak disalin ke source code untuk menghormati hak cipta standar.</div>
@endsection
