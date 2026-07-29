@extends('layouts.app') @section('title',$config['label']) @section('subtitle',$config['standard']) @section('content')
<a href="{{route('aggregate-tests.menu',$aggregate)}}" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Daftar pengujian</a>
<div class="d-flex justify-content-between mt-2 mb-4"><div><h3 class="fw-bold mb-1">{{$config['label']}}</h3><p class="text-secondary mb-0">{{$aggregate==='fine'?'Agregat Halus / Pasir':'Agregat Kasar / Kerikil'}} • {{$config['standard']}}</p></div><button type="button" class="btn btn-outline-primary align-self-start" id="add-observation"><i class="bi bi-plus-lg me-2"></i>Tambah Observasi</button></div>
<form method="post" action="{{route('aggregate-tests.store',[$aggregate,$test])}}">@csrf
<div class="card p-4 mb-4"><div class="row g-3"><div class="col-md-6"><label class="form-label">Proyek *</label><select class="form-select" name="project_id" required><option value="">Pilih proyek</option>@foreach($projects as $p)<option value="{{$p->id}}">{{$p->number}} — {{$p->name}}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Sumber material</label><select class="form-select" name="material_source_id"><option value="">Pilih sumber</option>@foreach($materials as $m)<option value="{{$m->id}}">{{$m->code}} — {{$m->name}}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Nomor sampel *</label><input class="form-control" name="sample_number" required></div><div class="col-md-4"><label class="form-label">Tanggal uji *</label><input type="date" class="form-control" name="tested_at" value="{{date('Y-m-d')}}" required></div><div class="col-md-4"><label class="form-label">Petugas *</label><input class="form-control" name="technician" value="{{auth()->user()->name}}" required></div></div></div>
<div class="card overflow-hidden mb-4"><div class="p-3 border-bottom d-flex justify-content-between"><b>Data Pengujian</b><span class="small text-secondary">Observasi bertambah ke arah kanan →</span></div><div class="table-responsive"><table class="table table-bordered align-middle mb-0" id="observation-table"><thead><tr><th style="min-width:330px">Parameter</th><th class="text-center observation-head" style="min-width:190px">Observasi I</th></tr></thead><tbody>
@foreach($config['fields'] as [$name,$label,$unit])<tr><td><b>{{$loop->iteration}}. {{$label}}</b>@if($unit)<span class="text-secondary small ms-1">({{$unit}})</span>@endif</td><td class="observation-cell"><input type="number" step="any" min="0" class="form-control text-end" name="observations[0][{{$name}}]" required></td></tr>@endforeach
</tbody></table></div></div>
<div class="card p-4 mb-4"><label class="form-label">Catatan</label><textarea class="form-control" name="notes"></textarea></div><div class="text-end"><button class="btn btn-primary btn-lg"><i class="bi bi-calculator me-2"></i>Hitung dan Simpan</button></div></form>
@endsection
@push('scripts')<script>
let observationCount=1; const roman=n=>['I','II','III','IV','V','VI','VII','VIII','IX','X'][n-1]||n;
document.getElementById('add-observation').addEventListener('click',()=>{
 observationCount++; const table=document.getElementById('observation-table');
 const th=document.createElement('th'); th.className='text-center observation-head'; th.style.minWidth='190px'; th.textContent='Observasi '+roman(observationCount); table.tHead.rows[0].appendChild(th);
 [...table.tBodies[0].rows].forEach(row=>{const source=row.querySelector('input');const td=document.createElement('td');td.className='observation-cell';const input=source.cloneNode();input.value='';input.name=source.name.replace(/observations\[\d+\]/,`observations[${observationCount-1}]`);td.appendChild(input);row.appendChild(td);});
});
</script>@endpush
