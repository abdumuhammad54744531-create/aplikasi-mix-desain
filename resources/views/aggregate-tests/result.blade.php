@extends('layouts.app') @section('title','Hasil '.$config['label']) @section('subtitle',$run->test_number) @section('content')
@php
$resultLabels=[
 'moisture'=>'Kadar air','silt'=>'Kadar lumpur','bulk_dry'=>'Berat jenis curah kering',
 'bulk_ssd'=>'Berat jenis curah SSD','apparent'=>'Berat jenis semu','absorption'=>'Penyerapan',
 'bulk_density'=>'Berat isi','voids'=>'Rongga','mass_total'=>'Total massa',
 'mass_difference'=>'Selisih massa','fineness_modulus'=>'Modulus kehalusan','abrasion'=>'Keausan',
];
@endphp
<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Data berhasil dihitung dan disimpan sebagai draf.</div>
<div class="d-flex justify-content-between mb-4"><div><h3 class="fw-bold">{{$config['label']}}</h3><p class="text-secondary">{{$run->sample_number}} • {{$run->tested_at->format('d/m/Y')}}</p></div><a class="btn btn-outline-primary align-self-start" href="{{route('aggregate-tests.create',[$run->aggregate_type,$run->test_type])}}">Pengujian Baru</a></div>
<div class="card overflow-hidden mb-4"><table class="table mb-0"><thead><tr><th>Hasil</th>@foreach($run->results['observations'] as $o)<th>Observasi {{$o['number']}}</th>@endforeach<th>Rata-rata</th></tr></thead><tbody>
@foreach($run->results['averages'] as $key=>$avg)<tr><td><b>{{$resultLabels[$key]??ucwords(str_replace('_',' ',$key))}}</b></td>@foreach($run->results['observations'] as $o)<td>{{number_format($o['values'][$key],3,',','.')}}</td>@endforeach<td class="fw-bold bg-light">{{number_format($avg,3,',','.')}}</td></tr>@endforeach
</tbody></table></div>
<div class="card p-4"><div class="small text-secondary">Rumus / metode</div><div class="mt-2">{{$run->results['formula']}}</div><div class="alert alert-warning mt-3 mb-0">Status memenuhi belum ditentukan sampai nilai batas resmi dimasukkan pada tabel referensi.</div></div>
@endsection
