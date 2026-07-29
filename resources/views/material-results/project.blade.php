@extends('layouts.app')
@section('title','Hasil '.$project->name)
@section('subtitle',$project->number)
@section('content')
<a href="{{route('material-results.index')}}" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Daftar proyek</a>
<div class="mt-2 mb-4"><h3 class="fw-bold">{{$project->name}}</h3><p class="text-secondary">{{$project->location}} · {{$project->concrete_grade}}</p></div>

<h4 class="fw-bold mb-3">1. Pemeriksaan Seluruh Material</h4>
<div class="card overflow-hidden mb-4"><div class="table-responsive"><table class="table align-middle mb-0">
<thead><tr><th>Material</th><th>Nomor Pengujian</th><th>Sampel</th><th>Tanggal</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
@foreach($materials as $label=>$tests)
 @php $archiveType=match($label){'Semen'=>'cement-tests','Air'=>'water-tests','Pasir'=>'fine-aggregate-tests','Kerikil'=>'coarse-aggregate-tests'}; @endphp
 @forelse($tests as $test)
 <tr><td><b>{{$label}}</b></td><td>{{$test->test_number}}</td><td>{{$test->sample_number}}</td><td>{{$test->tested_at?->format('d/m/Y')}}</td>
 <td><span class="badge {{$test->status==='disetujui'?'text-bg-success':($test->status==='diperiksa'?'text-bg-warning':'text-bg-secondary')}}">{{ucfirst($test->status)}}</span></td>
 <td class="text-end"><form method="post" action="{{route('archive.store',[$archiveType,$test->id])}}" onsubmit="return confirm('Pindahkan hasil pemeriksaan ini ke Arsip?')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger" title="Pindahkan ke Arsip"><i class="bi bi-trash"></i></button></form></td></tr>
 @empty
 <tr><td><b>{{$label}}</b></td><td colspan="5" class="text-danger">Belum diperiksa</td></tr>
 @endforelse
@endforeach
</tbody></table></div></div>

<h5 class="fw-bold mb-3">Rincian Pasir dan Kerikil</h5>
@forelse($runs as $key=>$group)
@php([$date,$sample,$type]=explode('|',$key))
<div class="card mb-4 overflow-hidden">
 <div class="p-3 bg-light border-bottom d-flex justify-content-between"><div><b>{{$type==='fine'?'Pemeriksaan Pasir':'Pemeriksaan Kerikil'}}</b><div class="small text-secondary">Sampel {{$sample}} · {{date('d/m/Y',strtotime($date))}}</div></div><span class="badge badge-soft align-self-center">{{$group->count()}} jenis pengujian</span></div>
 <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Pengujian</th><th>Nomor</th><th>Hasil Rata-rata</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
 @foreach($group as $run)
 <tr><td>{{ucwords(str_replace('-',' ',$run->test_type))}}</td><td>{{$run->test_number}}</td><td>@foreach(($run->results['averages']??[]) as $name=>$result)<div><span class="text-secondary small">{{ucwords(str_replace('_',' ',$name))}}:</span> <b>{{number_format($result,3,',','.')}}</b></div>@endforeach</td>
 <td><span class="badge {{$run->status==='disetujui'?'text-bg-success':($run->status==='diperiksa'?'text-bg-warning':'text-bg-secondary')}}">{{ucfirst($run->status)}}</span></td>
 <td class="text-end"><form method="post" action="{{route('archive.store',['aggregate-test-runs',$run->id])}}" onsubmit="return confirm('Pindahkan hasil pengujian ini ke Arsip?')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr>
 @endforeach
 </tbody></table></div>
</div>
@empty
<div class="card p-4 text-secondary">Belum ada rincian pengujian pasir atau kerikil.</div>
@endforelse

<h4 class="fw-bold my-3">2. Desain Campuran SNI 7656:2012</h4>
@forelse($mixDesigns as $mix)
<div class="card p-3 mb-3"><div class="d-flex justify-content-between gap-4"><div><b>{{$mix->number}}</b><div class="small text-secondary">{{$mix->work_date?->format('d/m/Y')}}</div><form class="mt-2" method="post" action="{{route('archive.store',['workflows',$mix->id])}}" onsubmit="return confirm('Pindahkan desain campuran ini ke Arsip?')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus</button></form></div><div class="text-end">@foreach(collect($mix->result_data)->reject(fn($v)=>is_array($v))->take(4) as $key=>$result)<div class="small">{{$key}}: <b>{{is_numeric($result)?number_format($result,3,',','.'):$result}}</b></div>@endforeach</div></div></div>
@empty
<div class="card p-4 text-secondary">Desain campuran belum dibuat.</div>
@endforelse

<h4 class="fw-bold my-3">3. Kuat Tekan</h4>
@forelse($strengthTests as $strength)
<div class="card p-3 mb-3"><div class="d-flex justify-content-between"><div><b>{{$strength->number}}</b><div class="small text-secondary">{{$strength->work_date?->format('d/m/Y')}} · {{count($strength->result_data['detail_rows']??[])}} benda uji</div><form class="mt-2" method="post" action="{{route('archive.store',['workflows',$strength->id])}}" onsubmit="return confirm('Pindahkan hasil kuat tekan ini ke Arsip?')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus</button></form></div><div class="text-end"><b>{{$strength->result_data['Status']??'-'}}</b><div class="small">Karakteristik: {{number_format($strength->result_data['Kuat tekan karakteristik (MPa)']??0,3,',','.')}} MPa</div></div></div></div>
@empty
<div class="card p-4 text-secondary">Pengujian kuat tekan belum tersedia.</div>
@endforelse
@endsection
