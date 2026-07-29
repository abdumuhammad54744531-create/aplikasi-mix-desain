@extends('layouts.app') @section('title','Pemeriksaan Material') @section('subtitle','Karakteristik bahan penyusun beton') @section('content')
<div class="mb-4"><h3 class="fw-bold">Pilih Material yang Diuji</h3><p class="text-secondary">Seluruh karakteristik bahan yang diperlukan untuk perencanaan campuran tersedia di bawah ini.</p></div>
@php($meta=[
'cement'=>['bi-box2','Semen','Berat jenis, kehalusan, konsistensi, waktu ikat, kuat tekan mortar, dan pemeriksaan visual.','#8b6f47'],
'water'=>['bi-droplet','Air','pH, lumpur, bahan organik, klorida, sulfat, zat padat terlarut, dan mortar pembanding.','#1689a7'],
'fine-aggregate'=>['bi-hourglass-split','Agregat Halus / Pasir','Berat jenis, penyerapan, berat isi, kadar air, kadar lumpur, modulus kehalusan, dan zona gradasi.','#d49a38'],
'coarse-aggregate'=>['bi-gem','Agregat Kasar / Kerikil','Ukuran maksimum, berat jenis, penyerapan, berat isi, kadar air, abrasi, pipih, lonjong, dan butir pecah.','#526b78']])
<div class="row g-4">@foreach($cards as $slug=>$card) @php($m=$meta[$slug])
<div class="col-md-6"><div class="card p-4 h-100 position-relative overflow-hidden"><div class="d-flex gap-3 align-items-start"><div class="metric-icon flex-shrink-0" style="color:{{$m[3]}};background:{{$m[3]}}18;width:54px;height:54px;font-size:25px"><i class="bi {{$m[0]}}"></i></div><div><div class="d-flex align-items-center gap-2"><h5 class="fw-bold mb-0">{{$m[1]}}</h5><span class="badge text-bg-light">{{$card['count']}} pengujian</span></div><p class="text-secondary small mt-2 mb-3">{{$m[2]}}</p><a class="btn btn-primary" href="{{in_array($slug,['fine-aggregate','coarse-aggregate'])?route('aggregate-tests.worksheet',$slug==='fine-aggregate'?'fine':'coarse'):route('material-tests.create',$slug)}}">Buka Lembar Pengujian <i class="bi bi-arrow-right ms-2"></i></a></div></div></div></div>
@endforeach</div>
<div class="card p-4 mt-4 border-start border-4 border-info"><div class="d-flex gap-3"><i class="bi bi-info-circle text-info fs-4"></i><div><b>Sumber pedoman</b><p class="text-secondary small mb-0">Pedoman Tata Cara Penentuan Campuran Beton Normal dengan Semen OPC, PPC dan PCC digunakan sebagai alur kebutuhan data campuran. Nilai persyaratan pengujian mengikuti tabel referensi resmi yang dimiliki laboratorium.</p></div></div></div>
@endsection
