<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan Desain Campuran Beton {{$project->number}}</title>
<style>
@page{size:A4;margin:{{$setting->margin_top}}mm {{$setting->margin_right}}mm {{$setting->margin_bottom}}mm {{$setting->margin_left}}mm}
*{box-sizing:border-box}body{margin:0;color:#111;background:#dfe5e7;font:{{$setting->font_size}}px "{{$setting->font_family}}",Arial,sans-serif;line-height:1.45}
.toolbar{position:fixed;right:20px;top:15px;z-index:9}.toolbar button{background:#087c70;color:#fff;border:0;border-radius:6px;padding:10px 16px}
.page{width:210mm;min-height:297mm;margin:18px auto;page-break-before:always;position:relative;padding:{{$setting->margin_top}}mm {{$setting->margin_right}}mm {{$setting->margin_bottom}}mm {{$setting->margin_left}}mm;background:#fff;box-shadow:0 5px 24px #1b303840}.page:first-of-type{page-break-before:auto}
.header{text-align:center;border-bottom:3px double #111;padding:0 75px 7px;margin-bottom:13px;position:relative;min-height:22mm}
.header-logo{position:absolute;top:0;height:19mm;object-fit:contain}.logo-position-left{left:0}.logo-position-center{left:50%;transform:translateX(-50%)}.logo-position-right{right:0}
.header h2,.header h3{margin:1px}.header p{margin:2px}.footer{position:absolute;bottom:1mm;left:3mm;right:3mm;border-top:1px solid #888;padding-top:3px;color:#555;font-size:8px}
.cover{display:flex;flex-direction:column;justify-content:center;text-align:center;page-break-before:auto!important}.cover h1{font-size:25px;line-height:1.35}.cover h2{font-size:18px}
.project-box{border:2px solid #111;padding:24px;margin:30px 20px;font-size:14px}.section-title{text-align:center;font-size:17px;margin:15px 0}
.chapter{font-size:14px;border-bottom:2px solid #111;padding-bottom:5px;margin-top:16px}.subchapter{font-size:12px;margin:13px 0 5px}
.info,.data{width:100%;border-collapse:collapse;margin:8px 0 14px}.info td{padding:4px;border-bottom:1px dotted #777}.data th,.data td{border:1px solid #333;padding:4px;vertical-align:top}.data th{background:#ddd}.data .dark{background:#183d4b;color:#fff}.data .soft{background:#edf5f3}.data .center{text-align:center}.data .right{text-align:right}
.small{font-size:8px}.tiny{font-size:7px}.muted{color:#666}.notice{border:1px solid #c99b2c;background:#fff5cf;padding:8px;margin:10px 0}
.result-box{border:2px solid #183d4b;padding:11px;margin:12px 0;background:#f4f9f8}.result-box strong{font-size:15px}
.two-col{display:flex;gap:12px}.two-col>div{width:50%}.signature{margin-left:auto;margin-top:24px;width:250px;text-align:center}.signature img{height:65px;max-width:180px;object-fit:contain}
.photo{border:1px solid #183d62;padding:7px;margin:10px 0;text-align:center}.photo img{display:block;max-width:100%;height:100mm;object-fit:contain;margin:auto}
.chart{width:100%;height:108mm;border:1px solid #aaa;margin:8px 0}.toc td:first-child{width:88%}.toc td:last-child{text-align:right}
.legal-row{display:flex;gap:12px;justify-content:space-around;align-items:flex-start;margin-top:22px}.legal-card{width:31%;font-size:8px}.legal-card img{width:27mm;height:27mm;display:block;margin:5px auto}
ol,ul{padding-left:22px}p.justify{text-align:justify}.nowrap{white-space:nowrap}
@media screen{.footer{left:{{$setting->margin_left}}mm;right:{{$setting->margin_right}}mm;bottom:6mm}}
@media screen and (max-width:850px){.page{width:calc(100% - 20px);min-height:auto;margin:10px;padding:20px 18px}.footer{position:static;margin-top:35px}.toolbar{right:14px;top:10px}}
@media print{body{background:#fff}.toolbar{display:none}.page{width:auto;min-height:238mm;margin:0;padding:2mm 3mm 10mm;background:transparent;box-shadow:none}.footer{left:3mm;right:3mm;bottom:1mm}}
</style>
</head>
<body>
<div class="toolbar"><button onclick="window.print()">Cetak / Simpan PDF</button></div>
@php
$labName=$laboratory?->name?:'LABORATORIUM BAHAN DAN STRUKTUR';
$institution=$laboratory?->institution?:'PROGRAM STUDI TEKNIK SIPIL';
$header=function()use($setting,$labName,$institution,$laboratory){
 return '<div class="header">'
 .($setting->logo_left?'<img class="header-logo logo-position-'.e($setting->logo_left_position).'" style="width:'.(float)$setting->logo_left_width.'mm" src="'.asset('storage/'.$setting->logo_left).'">':'')
 .($setting->logo_right?'<img class="header-logo logo-position-'.e($setting->logo_right_position).'" style="width:'.(float)$setting->logo_right_width.'mm" src="'.asset('storage/'.$setting->logo_right).'">':'')
 .'<h2>'.e($labName).'</h2><h3>'.e($institution).'</h3><p>'.e($laboratory?->address).'</p></div>';
};
$footer=fn()=>'<div class="footer">Laporan Desain Campuran Beton • '.e($project->number).' • Revisi '.(int)$project->report_revision.'</div>';
$labels=[
'test_number'=>'Nomor pengujian','sample_number'=>'Nomor sampel','tested_at'=>'Tanggal pengujian','received_at'=>'Tanggal diterima','technician'=>'Petugas pengujian','notes'=>'Catatan','brand'=>'Merek','producer'=>'Produsen','quarry'=>'Lokasi sumber','supplier'=>'Pemasok',
'moisture'=>'Kadar air','silt'=>'Kadar lumpur/lolos saringan No. 200','bulk_dry'=>'Berat jenis curah kering','bulk_ssd'=>'Berat jenis curah SSD','apparent'=>'Berat jenis semu','absorption'=>'Penyerapan','bulk_density'=>'Berat isi','voids'=>'Rongga','mass_total'=>'Total massa','mass_difference'=>'Selisih massa','fineness_modulus'=>'Modulus kehalusan','abrasion'=>'Keausan',
'fc'=>'Kuat tekan karakteristik','sd'=>'Deviasi standar','sd_additional'=>'Tambahan deviasi','test_count'=>'Jumlah hasil uji','deviation_factor'=>'Faktor pengali','margin'=>'Margin tambahan','fcr'=>'Kuat tekan rata-rata sasaran','slump_design'=>'Slump rencana','max_size'=>'Ukuran agregat maksimum','water'=>'Kebutuhan air','air_content'=>'Kadar udara','wc_ratio'=>'Rasio air-semen',
'cement'=>'Berat semen','fresh_density'=>'Berat beton segar','aggregate_mass_available'=>'Total berat agregat tersedia','coarse_ssd'=>'Agregat kasar SSD','fine_ssd'=>'Agregat halus SSD','fine_field'=>'Pasir kondisi lapangan','coarse_field'=>'Kerikil kondisi lapangan','water_added'=>'Air yang ditambahkan','total_fresh_mass'=>'Total massa beton segar',
'combined_fine_percent'=>'Persentase pasir gabungan','combined_coarse_percent'=>'Persentase kerikil gabungan','combined_total_percent'=>'Total agregat gabungan','combined_deviation'=>'Deviasi rata-rata gradasi','gradation_max_size'=>'Ukuran maksimum gradasi','gradation_curve'=>'Kurva gradasi','trial_volume_liter'=>'Volume campuran percobaan','waste'=>'Faktor kehilangan',
'cement_sg'=>'Berat jenis semen','coarse_sg'=>'Berat jenis kerikil','fine_sg'=>'Berat jenis pasir','coarse_density'=>'Berat isi kerikil','fine_fm'=>'Modulus kehalusan pasir','fine_moisture'=>'Kadar air pasir','coarse_moisture'=>'Kadar air kerikil','fine_absorption'=>'Penyerapan pasir','coarse_absorption'=>'Penyerapan kerikil','wc_ratio_calculated'=>'Rasio air-semen hasil perhitungan',
'fine_free_water'=>'Air bebas pasir','coarse_free_water'=>'Air bebas kerikil','vol_water'=>'Volume air','vol_cement'=>'Volume semen','vol_fine'=>'Volume pasir','vol_coarse'=>'Volume kerikil','vol_air'=>'Volume udara','ratio_cement'=>'Perbandingan semen','ratio_fine'=>'Perbandingan pasir','ratio_coarse'=>'Perbandingan kerikil','ratio_water'=>'Perbandingan air',
'trial_cement'=>'Semen untuk percobaan','trial_fine'=>'Pasir untuk percobaan','trial_coarse'=>'Kerikil untuk percobaan','trial_water'=>'Air untuk percobaan','sacks_per_m3'=>'Jumlah zak semen per m³','fine_per_sack'=>'Pasir per zak semen','coarse_per_sack'=>'Kerikil per zak semen','water_per_sack'=>'Air per zak semen',
'sample_mass'=>'Massa sampel awal','selected_zone'=>'Batas gradasi terpilih','r750'=>'Tertahan 75 mm','r375'=>'Tertahan 37,5 mm','r190'=>'Tertahan 19 mm','r095'=>'Tertahan 9,5 mm','r475'=>'Tertahan 4,75 mm','r236'=>'Tertahan 2,36 mm','r118'=>'Tertahan 1,18 mm','r060'=>'Tertahan 0,60 mm','r030'=>'Tertahan 0,30 mm','r015'=>'Tertahan 0,15 mm','pan'=>'Tertahan wadah dasar'
];
$pretty=fn($key)=>$labels[$key]??ucwords(str_replace('_',' ',$key));
$value=function($v){
 if($v instanceof \Carbon\CarbonInterface)return $v->format('d/m/Y');
 if(is_bool($v))return $v?'Ya':'Tidak';
 if(is_numeric($v))return number_format((float)$v,3,',','.');
 return ($v===null||$v==='')?'—':$v;
};
$latestMix=$mixDesigns->last();$mixInput=$latestMix?->input_data??[];$mixResult=$latestMix?->result_data??[];
$latestStrength=$strengthTests->last();$strengthResult=$latestStrength?->result_data??[];
$latestRun=function($aggregate,$type)use($aggregateRuns){return $aggregateRuns->where('aggregate_type',$aggregate)->where('test_type',$type)->last();};
$fineSource=$materialSources->firstWhere('type','fine')??$materialSources->firstWhere('type','pasir');
$coarseSource=$materialSources->firstWhere('type','coarse')??$materialSources->firstWhere('type','kerikil');
$cementSource=$materialSources->firstWhere('type','cement')??$materialSources->firstWhere('type','semen');
$waterSource=$materialSources->firstWhere('type','water')??$materialSources->firstWhere('type','air');
$runLabels=['moisture'=>'Pemeriksaan Kadar Air','silt'=>'Pemeriksaan Kadar Lumpur/Lolos No. 200','specific-gravity'=>'Pemeriksaan Berat Jenis dan Penyerapan','bulk-density'=>'Pemeriksaan Berat Isi','sieve'=>'Analisis Saringan','abrasion'=>'Keausan Agregat dengan Mesin Los Angeles'];
$sieveInfo=[
 'fine'=>[['3/8 in',9.5,'r095'],['No.4',4.75,'r475'],['No.8',2.36,'r236'],['No.16',1.18,'r118'],['No.30',.60,'r060'],['No.50',.30,'r030'],['No.100',.15,'r015'],['Wadah dasar',0,'pan']],
 'coarse'=>[['3 in',75,'r750'],['1½ in',37.5,'r375'],['¾ in',19,'r190'],['⅜ in',9.5,'r095'],['No.4',4.75,'r475'],['Wadah dasar',0,'pan']]
];
$fineLimits=['r095'=>[[100,100],[100,100],[100,100],[100,100]],'r475'=>[[90,100],[90,100],[90,100],[95,100]],'r236'=>[[60,95],[75,100],[85,100],[95,100]],'r118'=>[[30,70],[55,90],[75,100],[90,100]],'r060'=>[[15,34],[35,59],[60,79],[80,100]],'r030'=>[[5,20],[8,30],[12,40],[15,50]],'r015'=>[[0,10],[0,10],[0,10],[0,15]]];
$coarseLimits=['r750'=>[[null,null],[null,null],[100,100]],'r375'=>[[null,null],[100,100],[95,100]],'r190'=>[[100,100],[95,100],[35,70]],'r095'=>[[50,85],[30,60],[10,40]],'r475'=>[[0,10],[0,10],[0,5]]];
$sieveRows=function($run,$kind)use($sieveInfo){
 $obs=$run?->observations[0]??[];$sample=(float)($obs['sample_mass']??0);
 if($sample<=0)$sample=array_sum(array_map(fn($row)=>(float)($obs[$row[2]]??0),$sieveInfo[$kind]));
 $cumulative=0;$rows=[];
 foreach($sieveInfo[$kind] as [$label,$mm,$key]){
  $retained=(float)($obs[$key]??0);$percent=$sample>0?$retained/$sample*100:0;$cumulative+=$percent;
  $rows[$key]=compact('label','mm','retained','percent','cumulative')+['passing'=>max(0,100-$cumulative)];
 }
 return [$sample,$rows];
};
$chart=function($run,$kind,$zone=null)use($sieveRows,$fineLimits,$coarseLimits){
 [, $rows]=$sieveRows($run,$kind);$plot=array_values(array_filter($rows,fn($row)=>$row['mm']>0));
 $w=700;$h=330;$left=48;$right=18;$top=18;$bottom=42;$pw=$w-$left-$right;$ph=$h-$top-$bottom;$count=max(2,count($plot));
 $xy=function($i,$v)use($left,$top,$pw,$ph,$count){$x=$left+$i*$pw/($count-1);$y=$top+(100-$v)*$ph/100;return round($x,1).','.round($y,1);};
 $svg='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$w.' '.$h.'" class="chart"><rect width="100%" height="100%" fill="white"/>';
 for($i=0;$i<=10;$i++){$y=$top+$i*$ph/10;$v=100-$i*10;$svg.='<line x1="'.$left.'" y1="'.$y.'" x2="'.($w-$right).'" y2="'.$y.'" stroke="#d8e0e3" stroke-width="1"/><text x="5" y="'.($y+3).'" font-size="9">'.$v.'</text>';}
 $svg.='<line x1="'.$left.'" y1="'.$top.'" x2="'.$left.'" y2="'.($h-$bottom).'" stroke="#333"/><line x1="'.$left.'" y1="'.($h-$bottom).'" x2="'.($w-$right).'" y2="'.($h-$bottom).'" stroke="#333"/>';
 $points=[];foreach($plot as $i=>$row){$points[]=$xy($i,$row['passing']);$x=explode(',',$xy($i,0))[0];$svg.='<text x="'.$x.'" y="'.($h-25).'" text-anchor="middle" font-size="8">'.$row['label'].'</text><text x="'.$x.'" y="'.($h-13).'" text-anchor="middle" font-size="7">'.$row['mm'].' mm</text>';}
 if($run)$svg.='<polyline points="'.implode(' ',$points).'" fill="none" stroke="#e63946" stroke-width="3"/>';
 $limits=$kind==='fine'?$fineLimits:$coarseLimits;$zones=$zone?[intval($zone)]:range(1,$kind==='fine'?4:3);$colors=['#1d63d5','#149447','#e67e22','#7048e8'];
 foreach($zones as $z){$lo=[];$hi=[];foreach($plot as $i=>$row){$pair=$limits[array_search($row,$rows,true)]??[null,null];$bounds=$pair[$z-1]??[null,null];if($bounds[0]!==null)$lo[]=$xy($i,$bounds[0]);if($bounds[1]!==null)$hi[]=$xy($i,$bounds[1]);}if(count($lo)>1)$svg.='<polyline points="'.implode(' ',$lo).'" fill="none" stroke="'.$colors[$z-1].'" stroke-width="1.5" stroke-dasharray="5 4"/>';if(count($hi)>1)$svg.='<polyline points="'.implode(' ',$hi).'" fill="none" stroke="'.$colors[$z-1].'" stroke-width="1.5"/>';}
 $svg.='<text x="12" y="12" font-size="9">Persen lolos kumulatif (%)</text><text x="'.($w/2).'" y="'.($h-2).'" text-anchor="middle" font-size="9">Ukuran saringan</text><text x="'.($w-220).'" y="12" font-size="8" fill="#e63946">— Hasil pengujian</text></svg>';
 return '<img class="chart" alt="Grafik gradasi" src="data:image/svg+xml;base64,'.base64_encode($svg).'">';
};
$preface=$setting->preface_template?:'Puji syukur ke hadirat Tuhan Yang Maha Esa, laporan desain campuran beton untuk pekerjaan [PROYEK] telah diselesaikan. Laporan ini memuat data umum, pemeriksaan bahan, perhitungan desain campuran menurut SNI 7656:2012, hasil kuat tekan, kesimpulan, dokumentasi, dan dasar teori. Seluruh bagian dihimpun dalam satu dokumen agar dapat diperiksa secara berurutan.';
$preface=str_replace(['[PROYEK]','[PERUSAHAAN]','[TANGGAL]'],[$project->name,$project->owner?:$project->contractor,$project->legalized_at?->format('d/m/Y')?:date('d/m/Y')],$preface);
$missing='<div class="notice">Data pengujian belum tersedia pada proyek ini. Bagian tetap ditampilkan agar susunan laporan tidak terlewat.</div>';
@endphp

<section class="page cover">
{!!$header()!!}
<h1>LAPORAN HASIL<br>DESAIN CAMPURAN BETON</h1><h2>METODE SNI 7656:2012</h2>
<div class="project-box"><b>{{$project->name}}</b><br><br>{{$project->work_package?:'Paket pekerjaan belum diisi'}}<br>{{$project->location?:'Lokasi belum diisi'}}</div>
<h3>{{$project->owner?:($project->contractor?:'Pemohon belum diisi')}}</h3>
<p>Nomor laporan: {{$project->number}} • Revisi {{$project->report_revision}}</p>
@if($qrDataUri)<div style="margin:18px auto 0"><img src="{{$qrDataUri}}" style="width:28mm;height:28mm"><div><b>Pindai untuk memeriksa keaslian laporan</b></div></div>@endif
{!!$footer()!!}
</section>

<section class="page">{!!$header()!!}<h2 class="section-title">KATA PENGANTAR</h2>
<p class="justify" style="white-space:pre-line;line-height:1.8">{{$preface}}</p>
<p class="justify">Penyusun menyadari bahwa laporan ini perlu dibaca bersama data sumber dan kondisi pelaksanaan di lapangan. Koreksi atau perubahan data harus dilakukan melalui revisi laporan yang tercatat pada sistem.</p>
@php
$prefaceApproval=$approvalQrCodes->firstWhere('approval_role','mengetahui')?:$approvalQrCodes->first();
@endphp
@if($prefaceApproval)<div class="signature">{{$project->location?:'Lokasi penerbitan'}}, {{$prefaceApproval->approved_at?->format('d/m/Y')}}<br><b>{{strtoupper($prefaceApproval->approval_role)}}</b><br><img src="{{$prefaceApproval->qr_data_uri}}"><br><b>{{$prefaceApproval->user->name}}</b><br>{{$prefaceApproval->user->position?:$prefaceApproval->user->role}}<br>Ditandatangani secara elektronik</div>@endif
{!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title">DAFTAR ISI</h2>
<table class="info toc">
<tr><td>Kata Pengantar</td><td>i</td></tr><tr><td>Daftar Isi</td><td>ii</td></tr>
<tr><td><b>BAB I PENDAHULUAN</b></td><td>1</td></tr><tr><td>1.1 Data Umum Pekerjaan</td><td>1</td></tr><tr><td>1.2 Latar Belakang dan Lingkup Pekerjaan</td><td>1</td></tr><tr><td>1.3 Maksud dan Tujuan</td><td>2</td></tr><tr><td>1.4 Lokasi Pekerjaan</td><td>2</td></tr><tr><td>1.5 Data Bahan</td><td>2</td></tr><tr><td>1.6 Pemeriksaan dan Pengujian Laboratorium</td><td>3</td></tr>
<tr><td><b>BAB II HASIL DAN PEMBAHASAN</b></td><td>4</td></tr>
@foreach($reportMixTypes as $tocMixType)
@php
$tocSuffix=count($reportMixTypes)>1?($loop->iteration===1?'A':'B'):'';
$tocTitle=$tocMixType==='mix-design-2012-combined'?'Desain Campuran 2012 (Gradasi Gabungan)':'Desain Campuran 2012';
@endphp
<tr><td>2.1{{$tocSuffix}} Lembar Hasil {{$tocTitle}}</td><td>4</td></tr><tr><td>2.2{{$tocSuffix}} Pemakaian Bahan</td><td>5</td></tr><tr><td>2.3{{$tocSuffix}} Perhitungan {{$tocTitle}}</td><td>6</td></tr>
@endforeach
<tr><td>2.4 Hasil Pengujian Kuat Tekan</td><td>8</td></tr>
<tr><td><b>BAB III PENUTUP</b></td><td>9</td></tr><tr><td>3.1 Kesimpulan</td><td>9</td></tr><tr><td>3.2 Saran</td><td>9</td></tr>
<tr><td><b>LAMPIRAN HASIL PEMERIKSAAN MATERIAL</b></td><td>L-1</td></tr><tr><td>Grafik Gradasi Pasir dan Kerikil</td><td>L-9</td></tr><tr><td>Keausan Agregat Kasar</td><td>L-16</td></tr><tr><td>Dokumentasi</td><td>L-17</td></tr><tr><td>Dasar Teori dan Standar Acuan</td><td>L-18</td></tr>
</table>{!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title">BAB I<br>PENDAHULUAN</h2>
<h3 class="chapter">1.1 Data Umum Pekerjaan</h3><table class="info">
<tr><td>Nomor proyek/laporan</td><td><b>{{$project->number}}</b></td></tr><tr><td>Nama pekerjaan</td><td>{{$project->name}}</td></tr><tr><td>Paket pekerjaan</td><td>{{$project->work_package?:'—'}}</td></tr><tr><td>Pemilik pekerjaan</td><td>{{$project->owner?:'—'}}</td></tr><tr><td>Kontraktor pelaksana</td><td>{{$project->contractor?:'—'}}</td></tr><tr><td>Konsultan</td><td>{{$project->consultant?:'—'}}</td></tr><tr><td>Nomor/tanggal kontrak</td><td>{{$project->contract_number?:'—'}} / {{$project->contract_date?->format('d/m/Y')?:'—'}}</td></tr><tr><td>Jangka waktu</td><td>{{$project->start_date?->format('d/m/Y')?:'—'}} s.d. {{$project->end_date?->format('d/m/Y')?:'—'}}</td></tr><tr><td>Mutu beton rencana</td><td>{{$project->concrete_grade?:($mixInput['fc']??'—')}}</td></tr><tr><td>Jenis konstruksi</td><td>{{$project->construction_type?:'—'}}</td></tr>
</table>
<h3 class="chapter">1.2 Latar Belakang dan Lingkup Pekerjaan</h3>
<p class="justify">Desain campuran beton diperlukan untuk menentukan perbandingan bahan yang dapat mencapai kuat tekan, kelecakan, keawetan, dan kemudahan pelaksanaan sesuai kebutuhan pekerjaan. Lingkup laporan meliputi identifikasi bahan, pemeriksaan sifat bahan, analisis gradasi, perhitungan proporsi campuran, koreksi kadar air, campuran percobaan, dan evaluasi kuat tekan.</p>{!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title">BAB I<br>PENDAHULUAN (LANJUTAN)</h2>
<h3 class="chapter">1.3 Maksud dan Tujuan</h3><ol><li>Memperoleh proporsi semen, air, pasir, dan kerikil untuk satu meter kubik beton.</li><li>Memenuhi mutu beton dan slump rencana sesuai data proyek.</li><li>Menetapkan koreksi bahan berdasarkan kadar air dan penyerapan agregat.</li><li>Menyajikan dasar pemeriksaan dan evaluasi hasil campuran secara terdokumentasi.</li></ol>
<h3 class="chapter">1.4 Lokasi Pekerjaan</h3><p>{{$project->location?:'Lokasi pekerjaan belum diisi pada data proyek.'}}</p>
<h3 class="chapter">1.5 Data-data Bahan</h3><table class="data"><tr><th>Bahan</th><th>Nama/Merek</th><th>Produsen/Sumber</th><th>Pemasok</th></tr>
@foreach([['Semen',$cementSource],['Air',$waterSource],['Agregat halus/pasir',$fineSource],['Agregat kasar/kerikil',$coarseSource]] as [$name,$source])<tr><td>{{$name}}</td><td>{{$source?->brand?:($source?->name?:'Belum diisi')}}</td><td>{{$source?->producer?:($source?->quarry?:'Belum diisi')}}</td><td>{{$source?->supplier?:'—'}}</td></tr>@endforeach
</table>
<h3 class="chapter">1.6 Pemeriksaan dan Pengujian di Laboratorium</h3><p class="justify">Pemeriksaan mencakup kadar air, kadar lumpur, berat jenis dan penyerapan, berat isi, analisis saringan, modulus kehalusan, serta keausan agregat kasar. Hasil lengkap disajikan pada lampiran dan digunakan sebagai dasar perhitungan desain campuran.</p>{!!$footer()!!}</section>

@foreach($reportMixTypes as $reportMixType)
@php
$latestMix=$mixDesigns->where('type',$reportMixType)->last();
$mixInput=$latestMix?->input_data??[];
$mixResult=$latestMix?->result_data??[];
$mixReportTitle=$reportMixType==='mix-design-2012-combined'?'DESAIN CAMPURAN 2012 (GRADASI GABUNGAN)':'DESAIN CAMPURAN 2012';
$mixSectionSuffix=count($reportMixTypes)>1?($loop->iteration===1?'A':'B'):'';
@endphp
<section class="page">{!!$header()!!}<h2 class="section-title">BAB II<br>HASIL DAN PEMBAHASAN</h2><h3 class="chapter">2.1{{$mixSectionSuffix}} Lembar Hasil {{$mixReportTitle}}</h3>
<p>Kepada Yth.<br><b>{{$project->owner?:($project->contractor?:'Pemohon pekerjaan')}}</b><br>di tempat</p>
<p class="justify">Berdasarkan pemeriksaan bahan dan perhitungan desain campuran beton untuk pekerjaan <b>{{$project->name}}</b>, diperoleh komposisi rencana sebagai berikut.</p>
@if($latestMix)
<div class="result-box"><div>Nomor desain: <b>{{$latestMix->number}}</b> • Tanggal: {{$latestMix->work_date?->format('d/m/Y')}}</div><strong>Komposisi bahan untuk 1 m³ beton</strong></div>
<table class="data"><tr><th>Bahan</th><th>Kondisi SSD (kg)</th><th>Kondisi lapangan (kg)</th><th>Perbandingan berat</th></tr>
<tr><td>Semen</td><td class="right">{{$value($mixResult['cement']??null)}}</td><td class="right">{{$value($mixResult['cement']??null)}}</td><td class="right">1,000</td></tr>
<tr><td>Air</td><td class="right">{{$value($mixInput['water']??null)}}</td><td class="right">{{$value($mixResult['water_added']??null)}}</td><td class="right">{{$value($mixResult['ratio_water']??null)}}</td></tr>
<tr><td>Agregat halus/pasir</td><td class="right">{{$value($mixResult['fine_ssd']??null)}}</td><td class="right">{{$value($mixResult['fine_field']??null)}}</td><td class="right">{{$value($mixResult['ratio_fine']??null)}}</td></tr>
<tr><td>Agregat kasar/kerikil</td><td class="right">{{$value($mixResult['coarse_ssd']??null)}}</td><td class="right">{{$value($mixResult['coarse_field']??null)}}</td><td class="right">{{$value($mixResult['ratio_coarse']??null)}}</td></tr>
<tr><th>Jumlah</th><th></th><th class="right">{{$value($mixResult['total_fresh_mass']??null)}}</th><th></th></tr></table>
<div class="two-col"><div><b>Mutu rencana:</b> {{$value($mixInput['fc']??null)}} MPa<br><b>Slump rencana:</b> {{$value($mixInput['slump_design']??null)}} mm<br><b>Ukuran agregat maksimum:</b> {{$value($mixInput['max_size']??null)}} mm</div><div><b>Rasio air-semen:</b> {{$value($mixResult['wc_ratio_calculated']??null)}}<br><b>Pasir optimum:</b> {{$value($mixResult['combined_fine_percent']??null)}} %<br><b>Kerikil optimum:</b> {{$value($mixResult['combined_coarse_percent']??null)}} %</div></div>
@else {!!$missing!!} @endif
<p class="small"><b>Catatan:</b> Proporsi harus dikoreksi kembali apabila kadar air agregat, sumber bahan, gradasi, atau kondisi pelaksanaan berubah.</p>{!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title">2.2{{$mixSectionSuffix}} PEMAKAIAN BAHAN<br>{{$mixReportTitle}}</h2>
@if($latestMix)
<table class="data"><tr><th>No.</th><th>Uraian</th><th>Nilai</th><th>Satuan</th></tr>
@php
$usage=[
['Berat beton segar rencana',$mixInput['fresh_density']??$mixResult['total_fresh_mass']??null,'kg/m³'],
['Berat semen',$mixResult['cement']??null,'kg/m³'],['Berat air desain',$mixInput['water']??null,'kg/m³'],
['Berat agregat tersedia (beton − semen − air)',$mixResult['aggregate_mass_available']??null,'kg/m³'],
['Agregat halus kondisi SSD',$mixResult['fine_ssd']??null,'kg/m³'],['Agregat kasar kondisi SSD',$mixResult['coarse_ssd']??null,'kg/m³'],
['Pasir kondisi lapangan',$mixResult['fine_field']??null,'kg/m³'],['Kerikil kondisi lapangan',$mixResult['coarse_field']??null,'kg/m³'],
['Air yang ditambahkan',$mixResult['water_added']??null,'kg/m³'],['Jumlah zak semen 50 kg',$mixResult['sacks_per_m3']??null,'zak/m³'],
['Pasir per zak semen',$mixResult['fine_per_sack']??null,'kg/zak'],['Kerikil per zak semen',$mixResult['coarse_per_sack']??null,'kg/zak'],['Air per zak semen',$mixResult['water_per_sack']??null,'liter/zak']
];
@endphp
@foreach($usage as $i=>$row)<tr><td class="center">{{$i+1}}</td><td>{{$row[0]}}</td><td class="right">{{$value($row[1])}}</td><td>{{$row[2]}}</td></tr>@endforeach
</table>
<h3 class="chapter">Komposisi Campuran Percobaan</h3><table class="data"><tr><th>Volume percobaan</th><th>Semen</th><th>Air</th><th>Pasir</th><th>Kerikil</th></tr><tr><td class="center">{{$value($mixInput['trial_volume_liter']??20)}} liter</td><td class="right">{{$value($mixResult['trial_cement']??null)}} kg</td><td class="right">{{$value($mixResult['trial_water']??null)}} kg</td><td class="right">{{$value($mixResult['trial_fine']??null)}} kg</td><td class="right">{{$value($mixResult['trial_coarse']??null)}} kg</td></tr></table>
<div class="notice">Berat agregat dihitung dari berat beton rencana dikurangi berat semen dan air, kemudian dibagi menurut persentase pasir dan kerikil hasil analisis gradasi gabungan.</div>
@else {!!$missing!!} @endif {!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title">2.3{{$mixSectionSuffix}} PERHITUNGAN {{$mixReportTitle}}<br>SNI 7656:2012</h2>
@if($latestMix)<p>Nomor perhitungan: <b>{{$latestMix->number}}</b></p><table class="data small"><tr><th>No.</th><th>Langkah Perhitungan</th><th>Nilai</th></tr>
@php
$steps=[
['Kuat tekan karakteristik (f’c)',$mixInput['fc']??null,'MPa'],['Deviasi standar',$mixInput['sd']??null,'MPa'],['Margin tambahan',$mixResult['margin']??null,'MPa'],['Kuat tekan rata-rata sasaran (f’cr)',$mixResult['fcr']??null,'MPa'],
['Slump rencana',$mixInput['slump_design']??null,'mm'],['Ukuran maksimum agregat kasar',$mixInput['max_size']??null,'mm'],['Kebutuhan air',$mixInput['water']??null,'kg/m³'],['Kadar udara',$mixInput['air_content']??null,'%'],
['Rasio air-semen hasil interpolasi',$mixResult['wc_ratio_calculated']??null,''],['Berat semen = air ÷ rasio air-semen',$mixResult['cement']??null,'kg/m³'],['Berat beton segar',$mixInput['fresh_density']??null,'kg/m³'],['Berat agregat tersedia',$mixResult['aggregate_mass_available']??null,'kg/m³'],
['Persentase pasir gradasi gabungan',$mixResult['combined_fine_percent']??null,'%'],['Persentase kerikil gradasi gabungan',$mixResult['combined_coarse_percent']??null,'%'],['Deviasi rata-rata gradasi',$mixResult['combined_deviation']??null,'']
];
@endphp
@foreach($steps as $i=>$row)<tr><td class="center">{{$i+1}}</td><td>{{$row[0]}}</td><td class="right">{{$value($row[1])}} {{$row[2]}}</td></tr>@endforeach</table>
<p class="small">Metode perhitungan menggunakan volume absolut dan/atau pembagian massa agregat berdasarkan hasil analisis gradasi gabungan, kemudian dilakukan koreksi kelembapan bahan.</p>
@else {!!$missing!!} @endif {!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title">2.3{{$mixSectionSuffix}} PERHITUNGAN {{$mixReportTitle}}<br>(LANJUTAN)</h2>
@if($latestMix)
<h3 class="chapter">Volume Absolut dan Koreksi Kelembapan</h3><table class="data"><tr><th>Komponen</th><th>Massa (kg/m³)</th><th>Berat jenis</th><th>Volume (m³)</th></tr>
<tr><td>Air</td><td class="right">{{$value($mixInput['water']??null)}}</td><td class="right">1,000</td><td class="right">{{$value($mixResult['vol_water']??null)}}</td></tr>
<tr><td>Semen</td><td class="right">{{$value($mixResult['cement']??null)}}</td><td class="right">{{$value($mixInput['cement_sg']??null)}}</td><td class="right">{{$value($mixResult['vol_cement']??null)}}</td></tr>
<tr><td>Pasir</td><td class="right">{{$value($mixResult['fine_ssd']??null)}}</td><td class="right">{{$value($mixInput['fine_sg']??null)}}</td><td class="right">{{$value($mixResult['vol_fine']??null)}}</td></tr>
<tr><td>Kerikil</td><td class="right">{{$value($mixResult['coarse_ssd']??null)}}</td><td class="right">{{$value($mixInput['coarse_sg']??null)}}</td><td class="right">{{$value($mixResult['vol_coarse']??null)}}</td></tr>
<tr><td>Udara</td><td class="right">—</td><td class="right">—</td><td class="right">{{$value($mixResult['vol_air']??null)}}</td></tr></table>
<table class="data"><tr><th>Bahan</th><th>Kadar air (%)</th><th>Penyerapan (%)</th><th>Air bebas (kg)</th><th>Berat lapangan (kg)</th></tr>
<tr><td>Pasir</td><td class="right">{{$value($mixInput['fine_moisture']??null)}}</td><td class="right">{{$value($mixInput['fine_absorption']??null)}}</td><td class="right">{{$value($mixResult['fine_free_water']??null)}}</td><td class="right">{{$value($mixResult['fine_field']??null)}}</td></tr>
<tr><td>Kerikil</td><td class="right">{{$value($mixInput['coarse_moisture']??null)}}</td><td class="right">{{$value($mixInput['coarse_absorption']??null)}}</td><td class="right">{{$value($mixResult['coarse_free_water']??null)}}</td><td class="right">{{$value($mixResult['coarse_field']??null)}}</td></tr></table>
<h3 class="chapter">Perbandingan Akhir</h3><div class="result-box"><strong>Semen : Pasir : Kerikil : Air = 1 : {{$value($mixResult['ratio_fine']??null)}} : {{$value($mixResult['ratio_coarse']??null)}} : {{$value($mixResult['ratio_water']??null)}}</strong></div>
@else {!!$missing!!} @endif {!!$footer()!!}</section>
@endforeach
@php
$latestMix=$mixDesigns->last();
$mixInput=$latestMix?->input_data??[];
$mixResult=$latestMix?->result_data??[];
@endphp

<section class="page">{!!$header()!!}<h2 class="section-title">2.4 HASIL PENGUJIAN KUAT TEKAN BETON</h2>
@if($latestStrength)
<p>Nomor pengujian: <b>{{$latestStrength->number}}</b> • Sasaran: {{$value($latestStrength->input_data['target_fc']??null)}} MPa</p>
<table class="data tiny"><tr><th>No.</th><th>Tanggal buat</th><th>Tanggal uji</th><th>Umur (hari)</th><th>Diameter/Tinggi (mm)</th><th>Berat (kg)</th><th>Beban (kN)</th><th>Aktual (MPa)</th><th>Perkiraan 28 hari (MPa)</th><th>Mutu K (kg/cm²)</th></tr>
@forelse(($latestStrength->result_data['detail_rows']??[]) as $row)<tr><td>{{$row['number']}}</td><td>{{$row['cast_date']}}</td><td>{{$row['test_date']}}</td><td>{{$row['age_days']}}</td><td>{{$value($row['diameter'])}} / {{$value($row['height'])}}</td><td>{{$value($row['weight'])}}</td><td>{{$value($row['load_kn'])}}</td><td>{{$value($row['actual_mpa'])}}</td><td>{{$value($row['estimated_28_mpa'])}}</td><td>{{$value($row['estimated_k_kgcm2'])}}</td></tr>@empty<tr><td colspan="10" class="center">Rincian benda uji belum tersedia.</td></tr>@endforelse
</table>
<table class="data"><tr><th>Jumlah benda uji</th><td>{{$value($strengthResult['Jumlah benda uji']??null)}}</td></tr><tr><th>Rata-rata perkiraan umur 28 hari</th><td>{{$value($strengthResult['Rata-rata perkiraan 28 hari (MPa)']??null)}} MPa</td></tr><tr><th>Standar deviasi sampel</th><td>{{$value($strengthResult['Standar deviasi sampel (MPa)']??null)}} MPa</td></tr><tr><th>Kuat tekan karakteristik</th><td>{{$value($strengthResult['Kuat tekan karakteristik (MPa)']??null)}} MPa</td></tr><tr><th>Status</th><td><b>{{$strengthResult['Status']??'Belum dievaluasi'}}</b></td></tr></table>
@else {!!$missing!!} @endif {!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title">BAB III<br>PENUTUP</h2>
<h3 class="chapter">3.1 Kesimpulan</h3><ol>
<li>Desain campuran beton untuk pekerjaan <b>{{$project->name}}</b> disusun menggunakan metode SNI 7656:2012 berdasarkan data bahan yang tersimpan pada proyek.</li>
@if($latestMix)<li>Komposisi kondisi lapangan per meter kubik adalah semen {{$value($mixResult['cement']??null)}} kg, air {{$value($mixResult['water_added']??null)}} kg, pasir {{$value($mixResult['fine_field']??null)}} kg, dan kerikil {{$value($mixResult['coarse_field']??null)}} kg.</li><li>Rasio air-semen hasil perhitungan sebesar {{$value($mixResult['wc_ratio_calculated']??null)}} dengan perbandingan berat 1 : {{$value($mixResult['ratio_fine']??null)}} : {{$value($mixResult['ratio_coarse']??null)}}.</li>@else<li>Data desain campuran belum tersedia sehingga kesimpulan komposisi belum dapat ditetapkan.</li>@endif
@if($latestStrength)<li>Hasil evaluasi kuat tekan berstatus <b>{{$strengthResult['Status']??'belum dievaluasi'}}</b>, dengan kuat tekan karakteristik {{$value($strengthResult['Kuat tekan karakteristik (MPa)']??null)}} MPa.</li>@else<li>Hasil kuat tekan belum tersedia dan perlu dilengkapi setelah benda uji mencapai umur pengujian.</li>@endif
</ol>
<h3 class="chapter">3.2 Saran</h3><ol><li>Lakukan pengendalian kadar air agregat setiap kali produksi agar jumlah air efektif tetap sesuai desain.</li><li>Gunakan bahan dari sumber yang sama; perubahan sumber atau gradasi memerlukan pemeriksaan dan perhitungan ulang.</li><li>Laksanakan penakaran, pengadukan, pemadatan, perawatan, dan pengujian beton sesuai standar yang berlaku.</li><li>Campuran produksi harus dikonfirmasi melalui campuran percobaan dan evaluasi kuat tekan.</li></ol>
@if($approvalQrCodes->isNotEmpty())<div class="legal-row">@foreach($approvalQrCodes as $approval)<div class="legal-card"><b>{{strtoupper($approval->approval_role)}}</b><img src="{{$approval->qr_data_uri}}"><b>{{$approval->user->name}}</b><br>{{$approval->user->position?:$approval->user->role}}<br>{{$approval->approved_at?->format('d/m/Y H:i')}} WITA<br>Ditandatangani secara elektronik</div>@endforeach</div>@endif
{!!$footer()!!}</section>

@foreach(['fine'=>'AGREGAT HALUS/PASIR','coarse'=>'AGREGAT KASAR/KERIKIL'] as $aggregate=>$aggregateName)
 @foreach(['moisture','silt','specific-gravity','bulk-density'] as $testType)
 @php
 $run=$latestRun($aggregate,$testType);
 @endphp
 <section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN<br>{{$runLabels[$testType]}}<br>{{$aggregateName}}</h2>
 @if($run)
 <table class="info"><tr><td>Nomor pengujian</td><td><b>{{$run->test_number}}</b></td><td>Nomor sampel</td><td>{{$run->sample_number}}</td></tr><tr><td>Tanggal</td><td>{{$run->tested_at?->format('d/m/Y')}}</td><td>Petugas</td><td>{{$run->technician}}</td></tr></table>
 @php
 $fieldKeys=collect($run->observations??[])->flatMap(fn($o)=>array_keys($o))->reject(fn($k)=>str_starts_with($k,'zone_')||str_ends_with($k,'_sieve_mass')||str_ends_with($k,'_combined_mass'))->unique()->values();
 @endphp
 <table class="data small"><tr><th>Parameter pengamatan</th>@foreach($run->observations??[] as $i=>$obs)<th>Observasi {{$i+1}}</th>@endforeach</tr>
 @foreach($fieldKeys as $key)<tr><td>{{$pretty($key)}}</td>@foreach($run->observations??[] as $obs)<td class="right">{{$value($obs[$key]??null)}}</td>@endforeach</tr>@endforeach</table>
 <h3 class="chapter">Hasil Perhitungan</h3><table class="data"><tr><th>Parameter hasil</th>@foreach(($run->results['observations']??[]) as $obs)<th>Observasi {{$obs['number']??$loop->iteration}}</th>@endforeach<th>Rata-rata</th></tr>
 @forelse(($run->results['averages']??[]) as $key=>$avg)<tr><td>{{$pretty($key)}}</td>@foreach(($run->results['observations']??[]) as $obs)<td class="right">{{$value($obs['values'][$key]??null)}}</td>@endforeach<td class="right soft"><b>{{$value($avg)}}</b></td></tr>@empty<tr><td colspan="5" class="center">Hasil perhitungan belum tersedia.</td></tr>@endforelse</table>
 <p class="small"><b>Metode:</b> {{$run->results['formula']??'Sesuai metode pemeriksaan agregat yang berlaku.'}}</p>
 @else {!!$missing!!} @endif {!!$footer()!!}</section>
 @endforeach
@endforeach

@foreach(['Pemeriksaan Semen','Pemeriksaan Air'] as $sectionName)
@php
$tests=$materialTests[$sectionName]??collect();
$test=$tests->first();
@endphp
<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN<br>{{strtoupper($sectionName)}}</h2>
@if($test)<table class="info"><tr><td>Nomor pengujian</td><td><b>{{$test->test_number}}</b></td></tr><tr><td>Nomor sampel</td><td>{{$test->sample_number}}</td></tr><tr><td>Tanggal pengujian</td><td>{{$test->tested_at?->format('d/m/Y')}}</td></tr><tr><td>Petugas</td><td>{{$test->technician}}</td></tr></table>
<table class="data"><tr><th>Parameter</th><th>Hasil</th></tr>@foreach($test->getAttributes() as $key=>$raw)@if(!in_array($key,['id','project_id','test_number','sample_number','tested_at','technician','created_by','updated_by','deleted_at','created_at','updated_at','status'])&&$raw!==null)<tr><td>{{$pretty($key)}}</td><td>{{$value($raw)}}</td></tr>@endif @endforeach</table>
@else {!!$missing!!} @endif {!!$footer()!!}</section>
@endforeach

@foreach(['fine'=>'AGREGAT HALUS/PASIR','coarse'=>'AGREGAT KASAR/KERIKIL'] as $aggregate=>$aggregateName)
@php
$run=$latestRun($aggregate,'sieve');
$sieveResult=$sieveRows($run,$aggregate);
$sampleMass=$sieveResult[0];
$rows=$sieveResult[1];
@endphp
<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN<br>ANALISIS SARINGAN {{$aggregateName}}</h2>
@if($run)<p>Nomor: <b>{{$run->test_number}}</b> • Massa sampel: {{$value($sampleMass)}} g</p>@else {!!$missing!!} @endif
<table class="data small"><tr><th>Saringan</th><th>Ukuran (mm)</th><th>Massa tertahan (g)</th><th>% Tertahan</th><th>% Kumulatif</th><th>% Lolos</th></tr>
@foreach($rows as $row)<tr><td>{{$row['label']}}</td><td class="right">{{$value($row['mm'])}}</td><td class="right">{{$run?$value($row['retained']):'—'}}</td><td class="right">{{$run?$value($row['percent']):'—'}}</td><td class="right">{{$run?$value($row['cumulative']):'—'}}</td><td class="right"><b>{{$run?$value($row['passing']):'—'}}</b></td></tr>@endforeach</table>
{!!$chart($run,$aggregate)!!}<p class="small">Garis merah menunjukkan hasil pengujian; garis berwarna menunjukkan batas bawah dan atas gradasi.</p>{!!$footer()!!}</section>
@endforeach

@for($zone=1;$zone<=4;$zone++)
@php
$run=$latestRun('fine','sieve');
@endphp
<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN<br>BATAS GRADASI PASIR ZONA {{$zone}}</h2>{!!$chart($run,'fine',$zone)!!}
<table class="data small"><tr><th>Saringan</th><th>Batas bawah (%)</th><th>Batas atas (%)</th></tr>@foreach($sieveInfo['fine'] as [$label,$mm,$key])@if($mm>0)<tr><td>{{$label}} ({{$value($mm)}} mm)</td><td class="right">{{$value($fineLimits[$key][$zone-1][0]??null)}}</td><td class="right">{{$value($fineLimits[$key][$zone-1][1]??null)}}</td></tr>@endif @endforeach</table>{!!$footer()!!}</section>
@endfor

@foreach([1=>'10 mm',2=>'20 mm',3=>'40 mm'] as $zone=>$sizeName)
@php
$run=$latestRun('coarse','sieve');
@endphp
<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN<br>BATAS GRADASI KERIKIL MAKSIMUM {{$sizeName}}</h2>{!!$chart($run,'coarse',$zone)!!}
<table class="data small"><tr><th>Saringan</th><th>Batas bawah (%)</th><th>Batas atas (%)</th></tr>@foreach($sieveInfo['coarse'] as [$label,$mm,$key])@if($mm>0)<tr><td>{{$label}} ({{$value($mm)}} mm)</td><td class="right">{{$value($coarseLimits[$key][$zone-1][0]??null)}}</td><td class="right">{{$value($coarseLimits[$key][$zone-1][1]??null)}}</td></tr>@endif @endforeach</table>{!!$footer()!!}</section>
@endforeach

@php
$abrasionRun=$latestRun('coarse','abrasion');
@endphp
<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN<br>KEKERASAN/KEAUSAN AGREGAT KASAR<br>MESIN LOS ANGELES</h2>
@if($abrasionRun)
<table class="info"><tr><td>Nomor pengujian</td><td><b>{{$abrasionRun->test_number}}</b></td></tr><tr><td>Nomor sampel</td><td>{{$abrasionRun->sample_number}}</td></tr><tr><td>Tanggal</td><td>{{$abrasionRun->tested_at?->format('d/m/Y')}}</td></tr></table>
<table class="data"><tr><th>Parameter</th>@foreach($abrasionRun->observations??[] as $i=>$obs)<th>Observasi {{$i+1}}</th>@endforeach</tr>
@php
$abrasionKeys=collect($abrasionRun->observations??[])->flatMap(fn($o)=>array_keys($o))->unique();
@endphp
@foreach($abrasionKeys as $key)<tr><td>{{$pretty($key)}}</td>@foreach($abrasionRun->observations??[] as $obs)<td class="right">{{$value($obs[$key]??null)}}</td>@endforeach</tr>@endforeach
@foreach(($abrasionRun->results['averages']??[]) as $key=>$avg)<tr><th>{{$pretty($key)}} rata-rata</th><th colspan="{{max(1,count($abrasionRun->observations??[]))}}" class="right">{{$value($avg)}}</th></tr>@endforeach</table>
<p><b>Kesimpulan:</b> Nilai keausan hasil pengujian harus dibandingkan dengan persyaratan spesifikasi teknis pekerjaan.</p>
@else {!!$missing!!} @endif {!!$footer()!!}</section>

@if($documents->isNotEmpty())
@foreach($documents as $module=>$photos)@foreach($photos->chunk(2) as $chunk)
<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN DOKUMENTASI<br>{{\App\Http\Controllers\TestDocumentationController::MODULES[$module]??$pretty($module)}}</h2>
@foreach($chunk as $photo)<div class="photo"><img src="{{asset('storage/'.$photo->photo_path)}}"><b>{{$photo->title}}</b><div class="muted">{{$photo->documented_at?->format('d/m/Y')}} • {{$photo->description}}</div></div>@endforeach {!!$footer()!!}</section>
@endforeach @endforeach
@else
<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN DOKUMENTASI</h2>{!!$missing!!}<div class="photo" style="height:55mm;padding-top:23mm">Tempat foto dokumentasi pemeriksaan bahan</div><div class="photo" style="height:55mm;padding-top:23mm">Tempat foto dokumentasi campuran dan benda uji</div>{!!$footer()!!}</section>
@endif

<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN<br>DASAR TEORI DAN STANDAR ACUAN</h2>
<h3 class="chapter">A. Pengertian Desain Campuran Beton</h3><p class="justify">Desain campuran beton adalah proses penentuan proporsi bahan penyusun beton agar diperoleh sifat beton segar dan beton keras yang memenuhi kebutuhan struktur dengan penggunaan bahan yang wajar.</p>
<h3 class="chapter">B. Tujuan</h3><ul><li>Mencapai kuat tekan rata-rata sasaran dan mutu karakteristik.</li><li>Memperoleh kelecakan sesuai metode penempatan dan pemadatan.</li><li>Menjaga durabilitas melalui pembatasan rasio air-semen dan pengendalian bahan.</li><li>Mendapatkan campuran yang seragam, ekonomis, dan dapat diproduksi.</li></ul>
<h3 class="chapter">C. Bahan Penyusun</h3><p class="justify"><b>Semen</b> berfungsi sebagai bahan pengikat; <b>air</b> memicu hidrasi dan memberi kelecakan; <b>agregat halus</b> mengisi rongga; <b>agregat kasar</b> membentuk kerangka utama; bahan tambah digunakan hanya bila direncanakan dan dikendalikan.</p>
<h3 class="chapter">D. Parameter Penting</h3><p class="justify">Kuat tekan rencana, deviasi standar, slump, ukuran agregat maksimum, gradasi, berat jenis, penyerapan, kadar air, berat isi, kadar udara, dan rasio air-semen merupakan parameter yang saling memengaruhi.</p>{!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN<br>METODE PELAKSANAAN DAN DAFTAR ACUAN</h2>
<h3 class="chapter">E. Urutan Penerapan di Lapangan</h3><ol><li>Verifikasi sumber dan kondisi bahan sebelum produksi.</li><li>Ukur kadar air pasir dan kerikil, kemudian lakukan koreksi berat bahan dan air.</li><li>Takar bahan berdasarkan berat, bukan perkiraan volume, kecuali sudah dikalibrasi.</li><li>Aduk hingga homogen, periksa slump, suhu, dan berat isi beton segar.</li><li>Buat, padatkan, identifikasi, dan rawat benda uji.</li><li>Uji kuat tekan pada umur yang ditetapkan dan evaluasi hasil terhadap sasaran.</li></ol>
<h3 class="chapter">F. Standar Acuan</h3><table class="data"><tr><th>Standar</th><th>Ruang Lingkup</th></tr><tr><td>SNI 7656:2012</td><td>Tata cara pemilihan campuran beton normal, beton berat, dan beton massa.</td></tr><tr><td>SNI ASTM C136:2012</td><td>Metode uji analisis saringan agregat halus dan agregat kasar.</td></tr><tr><td>SNI 1970 dan SNI 1969</td><td>Berat jenis dan penyerapan agregat halus serta agregat kasar.</td></tr><tr><td>SNI 2417</td><td>Keausan agregat dengan mesin Los Angeles.</td></tr><tr><td>SNI 1972</td><td>Metode uji slump beton.</td></tr><tr><td>SNI 1974</td><td>Metode pengujian kuat tekan beton dengan benda uji silinder.</td></tr></table>
<div class="notice">Gunakan edisi standar dan spesifikasi kontrak yang berlaku pada tanggal pelaksanaan. Apabila terdapat perbedaan, persyaratan proyek yang disahkan menjadi dasar evaluasi.</div>
{!!$footer()!!}</section>
</body>
</html>
