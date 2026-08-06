@extends('layouts.app') @section('title','Hasil '.$config['label']) @section('subtitle',$run->test_number) @section('content')
@php
$resultLabels=[
 'moisture'=>'Kadar air','silt'=>'Kadar lumpur','bulk_dry'=>'Berat jenis curah kering',
 'bulk_ssd'=>'Berat jenis curah SSD','apparent'=>'Berat jenis semu','absorption'=>'Penyerapan',
 'bulk_density'=>'Berat isi','voids'=>'Rongga','mass_total'=>'Total massa',
 'mass_difference'=>'Selisih massa','fineness_modulus'=>'Modulus kehalusan','abrasion'=>'Keausan',
];
@endphp
@if($run->results['valid']??false)<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Data lengkap berhasil dihitung dan disimpan sebagai draf.</div>@else<div class="alert alert-warning"><i class="bi bi-save me-2"></i>Data yang belum lengkap berhasil disimpan sebagai draf. Lengkapi kembali proyek ini untuk memperoleh hasil perhitungan.</div>@endif
<div class="d-flex justify-content-between mb-4"><div><h3 class="fw-bold">{{$config['label']}}</h3><p class="text-secondary">{{$run->sample_number}} • {{$run->tested_at->format('d/m/Y')}}</p></div><a class="btn btn-outline-primary align-self-start" href="{{route('aggregate-tests.create',[$run->aggregate_type,$run->test_type])}}">Pengujian Baru</a></div>
<div class="card overflow-hidden mb-4"><table class="table mb-0"><thead><tr><th>Hasil</th>@foreach($run->results['observations'] as $o)<th>Observasi {{$o['number']}}</th>@endforeach<th>Rata-rata</th></tr></thead><tbody>
@forelse($run->results['averages'] as $key=>$avg)<tr><td><b>{{$resultLabels[$key]??ucwords(str_replace('_',' ',$key))}}</b></td>@foreach($run->results['observations'] as $o) @php($value=data_get($o,'values.'.$key)) <td>{{$value===null?'Belum lengkap':number_format($value,3,',','.')}}</td>@endforeach<td class="fw-bold bg-light">{{number_format($avg,3,',','.')}}</td></tr>@empty<tr><td colspan="{{count($run->results['observations']??[])+2}}" class="text-center text-secondary py-4">Belum ada observasi lengkap untuk dihitung.</td></tr>@endforelse
</tbody></table></div>
<div class="card p-4"><div class="small text-secondary">Kode data masukan</div><div class="d-flex flex-wrap gap-2 mt-2">@foreach($config['fields'] as [$name,$label,$unit])<span class="border rounded px-2 py-1 small"><b>{{chr(64+$loop->iteration)}}.</b> {{$label}}</span>@endforeach</div><div class="small text-secondary mt-3">Alur perhitungan</div>@if($run->test_type==='sieve')<div class="small text-secondary mt-2">MT = jumlah seluruh massa tertahan; Selisih = A − MT; % kumulatif = massa tertahan kumulatif / A × 100; FM = jumlah % kumulatif saringan standar / 100.</div>@else<div class="row g-2 mt-1">@foreach($config['process'] as [$key,$code,$label,$formula,$unit])<div class="col-md-6"><div class="border rounded p-2 h-100"><span class="badge text-bg-primary me-1">{{$code}}</span><b class="small">{{$label}}</b><div class="small text-secondary mt-1">{{$code}} = {{$formula}}{{$unit?' ('.$unit.')':''}}</div></div></div>@endforeach</div>@endif<div class="mt-3">{{$run->results['formula']}}</div><div class="alert alert-warning mt-3 mb-0">Status memenuhi belum ditentukan sampai nilai batas resmi dimasukkan pada tabel referensi.</div></div>
@endsection
