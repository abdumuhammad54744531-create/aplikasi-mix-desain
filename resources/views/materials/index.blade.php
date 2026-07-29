@extends('layouts.app')
@section('title','Sumber Material')
@section('subtitle','Data induk bahan dan asal sampel')
@section('content')
@php $types=['Semen','Abu terbang','Terak','Abu silika','Air','Pasir','Kerikil','Batu pecah','Agregat berat','Bahan tambah kimia','Bahan tambah mineral']; $materialFields=[['name','Nama material',1],['brand','Merek',0],['producer','Produsen',0],['quarry','Lokasi sumber',0],['supplier','Pemasok',0],['sample_number','Nomor sampel',0],['batch_number','Nomor kelompok produksi',0],['condition','Kondisi material',0]]; @endphp
<div class="d-flex justify-content-between mb-3"><div><h3 class="fw-bold">Sumber Material</h3><p class="text-secondary">Identitas bahan untuk pemeriksaan dan desain campuran.</p></div><button class="btn btn-primary align-self-start" data-bs-toggle="modal" data-bs-target="#add"><i class="bi bi-plus-lg me-2"></i>Tambah Material</button></div>
<div class="card p-3"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Kode</th><th>Material</th><th>Sumber</th><th>Proyek</th><th>Sampel</th><th>Aksi</th></tr></thead><tbody>
@forelse($materials as $m)<tr><td><b>{{$m->code}}</b></td><td>{{$m->name}}<div class="small text-secondary">{{$m->type}} • {{$m->brand}}</div></td><td>{{$m->quarry??$m->producer??'—'}}<div class="small text-secondary">{{$m->supplier}}</div></td><td>{{$m->project->name??'Umum'}}</td><td>{{$m->sample_number??'—'}}</td><td><div class="d-flex gap-1"><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMaterial{{$m->id}}" title="Ubah material" aria-label="Ubah material"><i class="bi bi-pencil-square"></i></button><form method="post" action="{{route('materials.destroy',$m)}}" onsubmit="return confirm('Arsipkan material ini?')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger" title="Arsipkan" aria-label="Arsipkan material"><i class="bi bi-trash"></i></button></form></div></td></tr>
@empty<tr><td colspan="6" class="text-center text-secondary py-5">Belum ada sumber material.</td></tr>@endforelse
</tbody></table></div></div>

<div class="modal fade" id="add"><div class="modal-dialog modal-lg modal-dialog-scrollable"><form method="post" action="{{route('materials.store')}}" class="modal-content">@csrf
<div class="modal-header"><h5>Tambah Sumber Material</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Proyek</label><select class="form-select" name="project_id"><option value="">Umum</option>@foreach($projects as $p)<option value="{{$p->id}}">{{$p->number}} — {{$p->name}}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Kode</label><input class="form-control" name="code" required></div><div class="col-md-3"><label class="form-label">Jenis</label><select class="form-select" name="type" required>@foreach($types as $t)<option>{{$t}}</option>@endforeach</select></div>
@foreach($materialFields as $f)<div class="col-md-6"><label class="form-label">{{$f[1]}}</label><input class="form-control" name="{{$f[0]}}" {{$f[2]?'required':''}}></div>@endforeach
<div class="col-md-6"><label class="form-label">Tanggal pengambilan</label><input type="date" class="form-control" name="sampled_at"></div><div class="col-12"><label class="form-label">Catatan</label><textarea class="form-control" name="notes"></textarea></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Material</button></div></form></div></div>

@foreach($materials as $m)
<div class="modal fade" id="editMaterial{{$m->id}}"><div class="modal-dialog modal-lg modal-dialog-scrollable"><form method="post" action="{{route('materials.update',$m)}}" class="modal-content">@csrf @method('put')
<div class="modal-header"><h5>Ubah Sumber Material — {{$m->code}}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Proyek</label><select class="form-select" name="project_id"><option value="">Umum</option>@foreach($projects as $p)<option value="{{$p->id}}" @selected($m->project_id==$p->id)>{{$p->number}} — {{$p->name}}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Kode</label><input class="form-control" name="code" value="{{$m->code}}" required></div><div class="col-md-3"><label class="form-label">Jenis</label><select class="form-select" name="type" required>@foreach($types as $t)<option @selected($m->type===$t)>{{$t}}</option>@endforeach</select></div>
@foreach($materialFields as $f) @php $field=$f[0]; @endphp <div class="col-md-6"><label class="form-label">{{$f[1]}}</label><input class="form-control" name="{{$field}}" value="{{$m->$field}}" {{$f[2]?'required':''}}></div>@endforeach
<div class="col-md-6"><label class="form-label">Tanggal pengambilan</label><input type="date" class="form-control" name="sampled_at" value="{{optional($m->sampled_at)->format('Y-m-d')}}"></div><div class="col-12"><label class="form-label">Catatan</label><textarea class="form-control" name="notes">{{$m->notes}}</textarea></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan Perubahan</button></div></form></div></div>
@endforeach
@endsection
