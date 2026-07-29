@extends('layouts.app')
@section('title','Arsip')
@section('subtitle','Data terhapus dapat dipulihkan atau dihapus permanen')
@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
 <div><h3 class="fw-bold">Arsip Data</h3><p class="text-secondary mb-0">Data pada halaman ini belum hilang permanen. Pulihkan untuk mengembalikannya ke menu asal.</p></div>
 <span class="badge text-bg-warning"><i class="bi bi-exclamation-triangle me-1"></i>Hapus permanen tidak dapat dibatalkan</span>
</div>
@forelse($groups as $group)
<div class="card overflow-hidden mb-4">
 <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
  <h5 class="fw-bold mb-0">{{$group['label']}}</h5>
  <span class="badge badge-soft">{{$group['items']->count()}} data</span>
 </div>
 <div class="table-responsive"><table class="table align-middle mb-0">
  <thead><tr><th>Identitas</th><th>Proyek / Keterangan</th><th>Diarsipkan</th><th class="text-end">Aksi</th></tr></thead>
  <tbody>
  @foreach($group['items'] as $item)
  <tr>
   <td><b>{{$item->number ?? $item->test_number ?? $item->code ?? $item->title ?? ('#'.$item->id)}}</b><div class="small text-secondary">{{$item->name ?? $item->type ?? $item->module ?? ''}}</div></td>
   <td>{{$item->project?->name ?? $item->sample_number ?? $item->notes ?? '—'}}</td>
   <td>{{$item->deleted_at?->format('d/m/Y H:i')}}</td>
   <td><div class="d-flex justify-content-end gap-2">
    <form method="post" action="{{route('archive.restore',[$group['type'],$item->id])}}">@csrf @method('patch')
     <button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise me-1"></i>Pulihkan</button>
    </form>
    <form method="post" action="{{route('archive.destroy',[$group['type'],$item->id])}}" onsubmit="return confirm('Hapus permanen data ini? Tindakan ini tidak dapat dibatalkan.')">@csrf @method('delete')
     <button class="btn btn-sm btn-danger"><i class="bi bi-trash3 me-1"></i>Hapus Permanen</button>
    </form>
   </div></td>
  </tr>
  @endforeach
  </tbody>
 </table></div>
</div>
@empty
<div class="card p-5 text-center"><i class="bi bi-archive display-5 text-secondary"></i><h5 class="fw-bold mt-3">Arsip masih kosong</h5><p class="text-secondary mb-0">Data yang dihapus dari menu aplikasi akan muncul di sini.</p></div>
@endforelse
@endsection
