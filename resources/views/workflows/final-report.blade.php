<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan Desain Campuran Beton {{$project->number}}</title>
<style>
@page{size:{{in_array(($reportPart??'full'),['chapter-four','strength'],true)?'A4 landscape':'A4'}};margin:{{$setting->margin_top}}mm {{$setting->margin_right}}mm {{$setting->margin_bottom}}mm {{$setting->margin_left}}mm}
*{box-sizing:border-box}body{margin:0;color:#111;background:#dfe5e7;font:{{$setting->font_size}}px "{{$setting->font_family}}",Arial,sans-serif;line-height:1.45}
.toolbar{position:fixed;right:20px;top:15px;z-index:9}.toolbar a{display:inline-block;background:#087c70;color:#fff;border:0;border-radius:6px;padding:10px 16px;text-decoration:none;font-weight:600;box-shadow:0 2px 8px #132f3940}.toolbar a:hover{background:#066b61}
.page{width:210mm;min-height:297mm;margin:18px auto;page-break-before:always;position:relative;padding:{{$setting->margin_top}}mm {{$setting->margin_right}}mm {{$setting->margin_bottom}}mm {{$setting->margin_left}}mm;background:#fff;box-shadow:0 5px 24px #1b303840}.page:first-of-type{page-break-before:auto}
.header{text-align:center;border-bottom:3px double #111;padding:0 75px 7px;margin-bottom:13px;position:relative;min-height:22mm;page-break-inside:avoid;page-break-after:avoid}
.header-logo{position:absolute;top:0;height:19mm;object-fit:contain}.logo-position-left{left:0}.logo-position-center{left:50%;transform:translateX(-50%)}.logo-position-right{right:0}
.header h2,.header h3{margin:1px}.header p{margin:2px}.footer{position:absolute;bottom:1mm;left:3mm;right:3mm;border-top:1px solid #888;padding-top:3px;color:#555;font-size:8px}
.cover{display:flex;flex-direction:column;justify-content:center;text-align:center;page-break-before:auto!important}.cover h1{font-size:25px;line-height:1.35}.cover h2{font-size:18px}
.project-box{border:2px solid #111;padding:24px;margin:30px 20px;font-size:14px}.section-title{text-align:center;font-size:17px;margin:15px 0}.subchapter-title{text-align:left}
.chapter{font-size:14px;border-bottom:2px solid #111;padding-bottom:5px;margin-top:16px;text-align:left}.subchapter{font-size:12px;margin:13px 0 5px;text-align:left}
.info,.data{width:100%;border-collapse:collapse;margin:8px 0 14px}.info td{padding:4px;border-bottom:1px dotted #777}.data th,.data td{border:1px solid #333;padding:4px;vertical-align:top}.data th{background:#ddd}.data .dark{background:#183d4b;color:#fff}.data .soft{background:#edf5f3}.data .center{text-align:center}.data .right{text-align:right}
.small{font-size:8px}.tiny{font-size:7px}.muted{color:#666}.notice{border:1px solid #c99b2c;background:#fff5cf;padding:8px;margin:10px 0}
.result-box{border:2px solid #183d4b;padding:11px;margin:12px 0;background:#f4f9f8}.result-box strong{font-size:15px}
.two-col{display:flex;gap:12px}.two-col>div{width:50%}.signature{margin-left:auto;margin-top:24px;width:250px;text-align:center}.signature img{height:65px;max-width:180px;object-fit:contain}
.photo{border:1px solid #183d62;padding:7px;margin:10px 0;text-align:center}.photo img{display:block;max-width:100%;height:100mm;object-fit:contain;margin:auto}
.chart{width:100%;height:108mm;border:1px solid #aaa;margin:8px 0}.toc{font-size:8pt!important;line-height:1.02;table-layout:fixed}.toc td{padding:1.2px 3px!important;vertical-align:middle}.toc td:first-child{width:auto}.toc td:last-child{width:16mm;text-align:right;white-space:nowrap}.toc tr{page-break-inside:avoid}.toc b{letter-spacing:0}.toc .toc-indent{padding-left:6mm!important}.conclusion-table{font-size:7.4pt!important;line-height:1.08}.conclusion-table th,.conclusion-table td{padding:2px!important}
.approval-qr{width:29mm!important;height:29mm!important;object-fit:contain;display:block;margin:0 auto}
.page-landscape{page:strength-landscape;width:297mm;min-height:210mm;padding:10mm;background:#fff}.strength-landscape-table{font-size:7.2pt!important;line-height:1.1;table-layout:fixed}.strength-landscape-table th,.strength-landscape-table td{padding:3px 2px!important;white-space:normal}.strength-landscape-table th:nth-child(1){width:4%}.strength-landscape-table th:nth-child(2),.strength-landscape-table th:nth-child(3){width:9%}
.chapter-four-slump{width:100%;border-collapse:collapse;margin-bottom:3mm}.chapter-four-slump td{width:33.33%;border:1px solid #555;padding:2.5mm;text-align:center}.chapter-four-layout{position:static;width:100%;max-width:100%;margin-top:3mm;border-collapse:separate;border-spacing:0;table-layout:fixed}.chapter-four-layout>tbody>tr>td{vertical-align:top}.chapter-four-details{width:73%;padding-right:3mm}.chapter-four-summary{width:27%;padding-left:1mm}.chapter-four-details .data,.chapter-four-summary .data{width:100%;table-layout:fixed;font-size:7.6pt!important;line-height:1.2}.chapter-four-details .data th,.chapter-four-details .data td,.chapter-four-summary .data th,.chapter-four-summary .data td{padding:3.2px 2.5px!important;height:8mm;overflow-wrap:anywhere}.chapter-four-summary .data{margin-top:0}.chapter-four-summary .data th{width:64%;text-align:left}.chapter-four-summary .data td{white-space:nowrap}.chapter-four-page{line-height:1.2}.chapter-four-page .section-title{margin:3mm 0}.chapter-four-page .chapter{margin:2mm 0}.chapter-four-page>p{margin:2mm 0}.approval-page{page-break-before:always!important}.approval-page .info{width:100%}.signature-date{white-space:nowrap}
.mix-appendix-page{padding-top:5mm}.mix-sheet{border:1px solid #87939a;background:#fff;font-family:inherit;color:#24333a}.mix-sheet-head{text-align:center;border-bottom:2px solid #273b44;padding:7px}.mix-sheet-head h3{font-size:12px;margin:0}.mix-sheet-head p{font-size:7px;margin:1px 0}.mix-bar{background:#d9dcde;border-top:1px solid #73828a;border-bottom:1px solid #73828a;padding:4px 6px;font-size:8px;font-weight:bold}.mix-no{display:inline-block;background:#243f4c;color:#fff;border-radius:50%;width:15px;height:15px;line-height:15px;text-align:center;margin-right:5px}.mix-grid{width:100%;border-collapse:collapse;table-layout:fixed}.mix-grid td{border-right:1px solid #d7dcdf;border-bottom:1px solid #e0e4e6;padding:4px;vertical-align:top;height:13mm}.mix-label{display:block;color:#53656d;font-size:6.5px;font-weight:bold;margin-bottom:2px}.mix-input{display:block;border:1px solid #9ca8ad;background:#f0e9fa;padding:3px;font-size:8px;min-height:15px}.mix-result{display:block;border-left:3px solid #1b9a89;background:#eff7ef;color:#146b60;font-weight:bold;padding:3px;font-size:8px;min-height:15px}.mix-unit{color:#708087;font-size:6px}.mix-help{background:#fff9df;border-left:3px solid #e3b341;padding:4px;font-size:6.5px}.mix-formula{background:#eef3f5;border-top:1px solid #c7d1d5;padding:3px 6px;font-size:6px}.mix-table{width:100%;border-collapse:collapse;margin:4px 0;font-size:6.5px}.mix-table th,.mix-table td{border:1px solid #aab4b9;padding:2px;vertical-align:middle}.mix-table th{background:#d9dcde;text-align:center}.mix-table tr.selected td,.mix-table td.selected{background:#e4f4ef;font-weight:bold}.mix-summary{background:#102f3d;color:#fff;padding:5px;text-align:center}.mix-summary table{width:100%;border-collapse:collapse}.mix-summary td{width:25%;font-size:7px}.mix-summary strong{color:#66e0c7;font-size:10px}.mix-meta{width:100%;border-collapse:collapse}.mix-meta td{padding:3px 5px;font-size:7px;border-bottom:1px solid #d7dcdf}.mix-appendix-title{text-align:center;font-size:11px;margin:3px 0 5px}.mix-note{font-size:6.5px;color:#637780}.mix-composition th{background:#d9dcde}.mix-composition td{text-align:right}.mix-composition td:first-child{text-align:left}.mix-active{background:#bfe7db!important;color:#075f55;font-weight:bold}
.mix-combined-chart{display:block;width:100%;height:92mm;object-fit:contain;background:#fff;margin:2mm 0}
ol,ul{padding-left:22px}p.justify{text-align:justify}.nowrap{white-space:nowrap}
@media screen{.footer{left:{{$setting->margin_left}}mm;right:{{$setting->margin_right}}mm;bottom:6mm}}
@media screen and (max-width:850px){.page{width:calc(100% - 20px);min-height:auto;margin:10px;padding:20px 18px}.footer{position:static;margin-top:35px}.toolbar{right:14px;top:10px}}
@media print{body{background:#fff}.toolbar{display:none}.page{width:auto;min-height:238mm;margin:0;padding:2mm 3mm 10mm;background:transparent;box-shadow:none}.footer{left:3mm;right:3mm;bottom:1mm}}
:root{--report-body-size:{{$setting->font_size}}pt;--report-heading-size:{{$setting->report_heading_size}}pt;--report-subheading-size:{{$setting->report_subheading_size}}pt;--report-table-size:{{$setting->report_table_size}}pt;--report-caption-size:{{$setting->report_caption_size}}pt;--report-line-height:{{$setting->report_line_height}}}
body{font-family:"{{$setting->font_family}}",Arial,sans-serif;font-size:var(--report-body-size);line-height:var(--report-line-height)}
.header{border-bottom:0;padding:0 24mm {{$setting->header_to_line_gap}}mm;margin-bottom:{{$setting->line_to_content_gap}}mm;min-height:22mm}.header-logo{height:auto}.header-line-1,.header-line-2{margin-left:-24mm;margin-right:-24mm}.header-line-1{border-top:{{$setting->header_line_1_width}}pt solid #111;margin-top:0}.header-line-2{border-top:{{$setting->header_line_2_width}}pt solid #111;margin-top:{{$setting->header_line_gap}}mm}.header-address{font-size:var(--report-caption-size);margin-top:1mm}
.footer,.small{font-size:var(--report-caption-size)}.tiny,.data{font-size:var(--report-table-size)}.section-title{font-size:var(--report-heading-size);page-break-after:avoid}.chapter,.subchapter{font-size:var(--report-subheading-size);page-break-after:avoid}
.cover h1{font-size:calc(var(--report-heading-size) + 7pt)}.cover h2{font-size:calc(var(--report-heading-size) + 2pt)}.cover-project-name{text-transform:uppercase;font-weight:700}.project-box{font-size:var(--report-subheading-size)}
.info,.data,.notice,.legal-row{page-break-inside:avoid}.info td{padding:5px 8px}.info td:first-child{width:37%;font-weight:600}.data th,.data td{vertical-align:middle;text-align:center}.data .right{ text-align:center}.data .text-left{text-align:left}
.legal-card{font-size:var(--report-caption-size)}.approval-signature{height:28mm;max-width:55mm;object-fit:contain}.approval-stamp{height:24mm;max-width:45mm;object-fit:contain;margin-left:-12mm}.map-image{display:block;max-width:100%;max-height:125mm;object-fit:contain;margin:5mm auto}.map-caption{text-align:center;font-size:var(--report-caption-size)}
.mix-sheet-head h3,.mix-appendix-title{font-size:10pt}.mix-sheet-head p,.mix-input,.mix-result,.mix-help,.mix-formula,.mix-table,.mix-summary td,.mix-meta td,.mix-note{font-size:8pt}.mix-label,.mix-unit{font-size:7.5pt}.mix-table th,.mix-table td,.mix-composition td{text-align:center;vertical-align:middle}
@media print{body{font-size:var(--report-body-size)}.page{padding:0 0 10mm}.page-landscape{width:auto;min-height:180mm;padding:0 0 8mm}.chapter-four-layout{position:absolute;top:112mm;left:0;margin-top:0}.cover{min-height:210mm!important}.chart{height:82mm}}
</style>
</head>
<body>
@php
$reportPart=$reportPart??'full';
@endphp
@if($project->verification_code)
<div class="toolbar"><a href="{{route('public.download',$project->verification_code)}}">Unduh PDF</a></div>
@endif
@php
$headerLines=$setting->resolvedHeaderLines($laboratory);
$header=function()use($setting,$headerLines){
 $html='<div class="header">';
 foreach(['left','right'] as $side){
  if(!$setting->{'logo_'.$side})continue;
  $position=$setting->{'logo_'.$side.'_position'};$x=(float)$setting->{'logo_'.$side.'_x'};$y=(float)$setting->{'logo_'.$side.'_y'};
  $positionCss=$position==='center'?'left:calc(50% + '.$x.'mm);transform:translateX(-50%);':($position==='right'?'right:'.(-$x).'mm;':'left:'.$x.'mm;');
  $height=$setting->{'logo_'.$side.'_height'}?'height:'.(float)$setting->{'logo_'.$side.'_height'}.'mm;':'height:auto;';
  $html.='<img class="header-logo" style="'.$positionCss.'top:'.$y.'mm;width:'.(float)$setting->{'logo_'.$side.'_width'}.'mm;'.$height.'" src="'.asset('storage/'.$setting->{'logo_'.$side}).'">';
 }
 foreach($headerLines as $line)$html.='<div style="font-family:'.e($line['font']).';font-size:'.(float)$line['size'].'pt;font-weight:'.($line['bold']?700:400).';font-style:'.($line['italic']?'italic':'normal').';text-transform:'.($line['uppercase']?'uppercase':'none').';text-align:'.e($line['align']).';line-height:'.(float)$line['line_height'].';margin:'.(float)$line['margin_top'].'mm 0 '.(float)$line['margin_bottom'].'mm">'.e($line['text']).'</div>';
 $city=collect([$setting->examiner_city,$setting->examiner_province,$setting->examiner_postal_code])->filter()->join(', ');
 $contact=collect([$setting->examiner_phone,$setting->examiner_email,$setting->examiner_website])->filter()->join(' · ');
 $html.='<div class="header-address">'.collect([$setting->examiner_address,$city,$contact])->filter()->map(fn($line)=>e($line))->join('<br>').'</div>';
 if($setting->header_lines_enabled)$html.='<div class="header-line-1"></div><div class="header-line-2"></div>';
 return $html.'</div>';
};
$footer=fn()=>'<div class="footer">Laporan Desain Campuran Beton • '.e($project->number).' • Revisi '.(int)$project->report_revision.'</div>';
$labels=[
'test_number'=>'Nomor pengujian','sample_number'=>'Nomor sampel','tested_at'=>'Tanggal pengujian','received_at'=>'Tanggal diterima','technician'=>'Petugas pengujian','notes'=>'Catatan','brand'=>'Merek','producer'=>'Produsen','quarry'=>'Lokasi sumber','supplier'=>'Pemasok',
'moisture'=>'Kadar air','silt'=>'Kadar lumpur/lolos saringan No. 200','bulk_dry'=>'Berat jenis curah kering','bulk_ssd'=>'Berat jenis curah SSD','apparent'=>'Berat jenis semu','absorption'=>'Penyerapan','bulk_density'=>'Berat isi','voids'=>'Rongga','mass_total'=>'Total massa','mass_difference'=>'Selisih massa','fineness_modulus'=>'Modulus kehalusan','abrasion'=>'Keausan',
'fc'=>'Kuat tekan karakteristik','sd'=>'Deviasi standar','sd_additional'=>'Tambahan deviasi','test_count'=>'Jumlah hasil uji','deviation_factor'=>'Faktor pengali','margin'=>'Margin tambahan','fcr'=>'Kuat tekan rata-rata sasaran','slump_design'=>'Slump rencana','max_size'=>'Ukuran agregat maksimum','water'=>'Kebutuhan air','air_content'=>'Kadar udara','wc_ratio'=>'Rasio air-semen',
'cement'=>'Berat semen','fresh_density'=>'Berat beton segar','aggregate_mass_available'=>'Total berat agregat tersedia','coarse_ssd'=>'Agregat kasar SSD','fine_ssd'=>'Agregat halus SSD','fine_field'=>'Pasir kondisi lapangan','coarse_field'=>'Kerikil kondisi lapangan','water_added'=>'Air yang ditambahkan','total_fresh_mass'=>'Total massa beton segar',
'combined_fine_percent'=>'Persentase pasir gabungan','combined_coarse_percent'=>'Persentase kerikil gabungan','combined_total_percent'=>'Total agregat gabungan','combined_deviation'=>'Deviasi rata-rata gradasi','gradation_max_size'=>'Ukuran maksimum gradasi','gradation_curve'=>'Kurva gradasi','trial_cylinder_diameter_mm'=>'Diameter silinder trial','trial_cylinder_height_mm'=>'Tinggi silinder trial','trial_cylinder_count'=>'Jumlah silinder satu batch','trial_volume_liter'=>'Total volume silinder','trial_batch_volume_liter'=>'Volume adukan setelah kelebihan','waste'=>'Faktor kehilangan',
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
 return ($v===null||$v==='')?'-':$v;
};
$indonesianDate=function($date){
 if(!$date)return '-';
 $date=$date instanceof \Carbon\CarbonInterface?$date:\Carbon\Carbon::parse($date);
 $months=[1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
 return $date->format('d').' '.$months[(int)$date->format('n')].' '.$date->format('Y');
};
$signatureCity=$setting->examiner_city?:'Baubau';
$signatureDate=$project->legalized_at??$project->report_date??now();
$prefaceApproval=$approvalQrCodes->firstWhere('approval_role','mengetahui')?:$approvalQrCodes->first();
$latestMix=$mixDesigns->last();$mixInput=$latestMix?->input_data??[];$mixResult=$latestMix?->result_data??[];
$latestStrength=$strengthTests->last();$strengthResult=$latestStrength?->result_data??[];
$latestSlump=$slumpTests->last();$latestFresh=$freshTests->last();
$slumpActual=$latestSlump?->actual_slump_mm??data_get($latestFresh?->input_data,'actual_slump');
$slumpMin=$latestSlump?->minimum_slump_mm??($mixInput['slump_min']??null);$slumpMax=$latestSlump?->maximum_slump_mm??($mixInput['slump_max']??null);
$dms=function($value,$latitude=true){if($value===null)return '-';$absolute=abs((float)$value);$degrees=floor($absolute);$minutesFull=($absolute-$degrees)*60;$minutes=floor($minutesFull);$seconds=($minutesFull-$minutes)*60;$direction=$latitude?((float)$value<0?'LS':'LU'):((float)$value<0?'BB':'BT');return sprintf('%d° %d\' %.2f" %s',$degrees,$minutes,$seconds,$direction);};
$latestRun=function($aggregate,$type)use($aggregateRuns){return $aggregateRuns->where('aggregate_type',$aggregate)->where('test_type',$type)->last();};
$fineSource=$materialSources->firstWhere('type','fine')??$materialSources->firstWhere('type','pasir');
$coarseSource=$materialSources->firstWhere('type','coarse')??$materialSources->firstWhere('type','kerikil');
$cementSource=$materialSources->firstWhere('type','cement')??$materialSources->firstWhere('type','semen');
$waterSource=$materialSources->firstWhere('type','water')??$materialSources->firstWhere('type','air');
$runLabels=['moisture'=>'Pemeriksaan Kadar Air','silt'=>'Pemeriksaan Kadar Lumpur/Lolos No. 200','specific-gravity'=>'Pemeriksaan Berat Jenis dan Penyerapan','bulk-density'=>'Pemeriksaan Berat Isi','sieve'=>'Analisis Saringan','los-angeles'=>'Keausan Agregat dengan Mesin Los Angeles'];
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
$preface=str_replace(['[PROYEK]','[PERUSAHAAN]','[TANGGAL]'],[$project->name,$project->owner?:$project->contractor,$indonesianDate($signatureDate)],$preface);
$missing='<div class="notice">Data pengujian belum tersedia pada proyek ini. Bagian tetap ditampilkan agar susunan laporan tidak terlewat.</div>';
@endphp

@if(in_array($reportPart,['full','before-chapter-four'],true))
<section class="page cover">
{!!$header()!!}
<h1>LAPORAN HASIL<br>DESAIN CAMPURAN BETON</h1><h2>METODE SNI 7656:2012</h2>
<div class="project-box"><b class="cover-project-name">{{$project->name}}</b><br><br>{{$project->work_package?:'-'}}<br>{{$project->location_address?:($project->location?:'-')}}</div>
<h3>{{$project->owner?:($project->contractor?:'Pemohon belum diisi')}}</h3>
<p>Nomor laporan: {{$project->number}} • Revisi {{$project->report_revision}}</p>
@if($qrDataUri)<div style="margin:18px auto 0"><img src="{{$qrDataUri}}" style="width:28mm;height:28mm"><div><b>Pindai untuk memeriksa keaslian laporan</b></div></div>@endif
{!!$footer()!!}
</section>

<section class="page">{!!$header()!!}<h2 class="section-title">KATA PENGANTAR</h2>
<p class="justify" style="white-space:pre-line;line-height:1.8">{{$preface}}</p>
<p class="justify">Penyusun menyadari bahwa laporan ini perlu dibaca bersama data sumber dan kondisi pelaksanaan di lapangan. Koreksi atau perubahan data harus dilakukan melalui revisi laporan yang tercatat pada sistem.</p>
@if($prefaceApproval)<div class="signature"><span class="signature-date">{{$signatureCity}}, {{$indonesianDate($prefaceApproval->approved_at??$signatureDate)}}</span><br><b>MENYETUJUI</b><br><img class="approval-qr" src="{{$prefaceApproval->qr_data_uri}}" alt="QR persetujuan"><br><b>{{$prefaceApproval->user->name}}</b><br>{{$prefaceApproval->user->position?:$prefaceApproval->user->role}}<br>Ditandatangani secara elektronik</div>@endif
{!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title">DAFTAR ISI</h2>
@php
$tocCoreMaterialCount=collect(['fine','coarse'])->sum(fn($aggregate)=>collect(['moisture','silt','specific-gravity','bulk-density'])->filter(fn($type)=>$latestRun($aggregate,$type)!==null)->count());
$tocCementWaterCount=(int)(($materialTests['Pemeriksaan Semen']??collect())->isNotEmpty())+(int)(($materialTests['Pemeriksaan Air']??collect())->isNotEmpty());
$tocSieveCount=collect(['fine','coarse'])->filter(fn($aggregate)=>$latestRun($aggregate,'sieve')!==null)->count();
$tocFineSelected=data_get($latestRun('fine','sieve')?->observations,'0.selected_zone');
$tocFineZoneCount=$tocFineSelected==='all'?4:(in_array((int)$tocFineSelected,range(1,4),true)?1:0);
$tocCoarseSelected=data_get($latestRun('coarse','sieve')?->observations,'0.selected_zone');
$tocCoarseZoneCount=$tocCoarseSelected==='all'?3:(in_array((int)$tocCoarseSelected,range(1,3),true)?1:0);
$tocMaterialCount=$tocCoreMaterialCount+$tocCementWaterCount;
$tocGradationStart=1+$tocMaterialCount;
$tocAbrasionStart=$tocGradationStart+$tocSieveCount+$tocFineZoneCount+$tocCoarseZoneCount;
$tocCursor=$tocAbrasionStart+($latestRun('coarse','los-angeles')?1:0);
@endphp
<table class="info toc">
<tr><td>Kata Pengantar</td><td>i</td></tr><tr><td>Daftar Isi</td><td>ii</td></tr>
<tr><td><b>BAB I DATA DAN INFORMASI PEKERJAAN</b></td><td>1</td></tr><tr><td class="toc-indent">1.1 Data Umum Pekerjaan</td><td>1</td></tr><tr><td class="toc-indent">1.2 Latar Belakang dan Lingkup Pekerjaan</td><td>1</td></tr><tr><td class="toc-indent">1.3 Maksud dan Tujuan</td><td>2</td></tr><tr><td class="toc-indent">1.4 Lokasi Pekerjaan dan Peta</td><td>2</td></tr><tr><td class="toc-indent">1.5 Data Bahan</td><td>2</td></tr><tr><td class="toc-indent">1.6 Pemeriksaan dan Pengujian Laboratorium</td><td>3</td></tr>
<tr><td><b>BAB II PEMERIKSAAN MATERIAL</b></td><td>4</td></tr><tr><td class="toc-indent">2.1 Agregat Halus</td><td>4</td></tr><tr><td class="toc-indent">2.2 Agregat Kasar</td><td>4</td></tr><tr><td><b>BAB III PERENCANAAN / KOMPOSISI CAMPURAN</b></td><td>5</td></tr>
@foreach($reportMixTypes as $tocMixType)
@php
$tocSuffix=count($reportMixTypes)>1?($loop->iteration===1?'A':'B'):'';
$tocTitle=$tocMixType==='mix-design-2012-combined'?'Desain Campuran 2012 (Gradasi Gabungan)':'Desain Campuran 2012';
@endphp
<tr><td class="toc-indent">3.1{{$tocSuffix}} Lembar Hasil {{$tocTitle}}</td><td>5</td></tr><tr><td class="toc-indent">3.2{{$tocSuffix}} Pemakaian Bahan</td><td>6</td></tr><tr><td class="toc-indent">3.3{{$tocSuffix}} Perhitungan {{$tocTitle}}</td><td>7</td></tr>
@endforeach
<tr><td><b>BAB IV HASIL PENGUJIAN BETON</b></td><td>9</td></tr><tr><td class="toc-indent">4.1 Beton Segar / Slump</td><td>9</td></tr><tr><td class="toc-indent">4.2 Benda Uji</td><td>9</td></tr><tr><td class="toc-indent">4.3 Kuat Tekan Beton</td><td>9</td></tr>
<tr><td><b>BAB V PENUTUP</b></td><td>10</td></tr><tr><td class="toc-indent">5.1 Kesimpulan</td><td>10</td></tr><tr><td class="toc-indent">5.2 Saran</td><td>10</td></tr><tr><td><b>LEMBAR PENGESAHAN</b></td><td>11</td></tr>
@if($tocMaterialCount)<tr><td><b>LAMPIRAN HASIL PEMERIKSAAN MATERIAL</b></td><td>L-1</td></tr>@endif
@if($tocSieveCount+$tocFineZoneCount+$tocCoarseZoneCount)<tr><td>Grafik dan Batas Gradasi Pasir/Kerikil</td><td>L-{{$tocGradationStart}}</td></tr>@endif
@if($latestRun('coarse','los-angeles'))<tr><td>Keausan Agregat Kasar</td><td>L-{{$tocAbrasionStart}}</td></tr>@endif
@foreach($reportMixTypes as $tocMixType)<tr><td>Lampiran Perhitungan {{$tocMixType==='mix-design-2012-combined'?'Desain Campuran 2012 (Gradasi Gabungan)':'Desain Campuran 2012'}}</td><td>L-{{$tocCursor}}</td></tr>@php $tocCursor += $tocMixType==='mix-design-2012-combined'?5:4; @endphp @endforeach
@if($slumpActual!==null)<tr><td>Pemeriksaan Slump Beton Segar</td><td>L-{{$tocCursor}}</td></tr>@php $tocCursor++; @endphp @endif
@php $tocStrengthPages=$strengthTests->sum(fn($test)=>max(1,(int)ceil(count($test->result_data['detail_rows']??[])/10))); @endphp
@if($tocStrengthPages)<tr><td>Pengujian Kuat Tekan Beton (Landscape)</td><td>L-{{$tocCursor}}</td></tr>@php $tocCursor += $tocStrengthPages; @endphp @endif
@php $tocDocumentationPages=$documents->sum(fn($photos)=>(int)ceil($photos->count()/2)); @endphp
@if($documents->isNotEmpty())<tr><td>Dokumentasi</td><td>L-{{$tocCursor}}</td></tr>@php $tocCursor += $tocDocumentationPages; @endphp @endif
<tr><td>Dasar Teori dan Standar Acuan</td><td>L-{{$tocCursor}}</td></tr>
</table>{!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title">BAB I<br>DATA DAN INFORMASI PEKERJAAN</h2>
<h3 class="chapter">1.1 Data Umum Pekerjaan</h3><table class="info">
<tr><td>Nomor proyek/laporan</td><td><b>{{$project->number}}</b></td></tr><tr><td>Nama pekerjaan</td><td>{{$project->name}}</td></tr><tr><td>Paket pekerjaan</td><td>{{$project->work_package?:'-'}}</td></tr><tr><td>Pemilik pekerjaan</td><td>{{$project->owner?:'-'}}</td></tr><tr><td>Kontraktor pelaksana</td><td>{{$project->contractor?:'-'}}</td></tr><tr><td>Konsultan</td><td>{{$project->consultant?:'-'}}</td></tr><tr><td>Nomor/tanggal kontrak</td><td>{{$project->contract_number?:'-'}} / {{$project->contract_date?->format('d/m/Y')?:'-'}}</td></tr><tr><td>Jangka waktu</td><td>{{$project->start_date?->format('d/m/Y')?:'-'}} s.d. {{$project->end_date?->format('d/m/Y')?:'-'}}</td></tr><tr><td>Mutu beton rencana</td><td>{{$project->concrete_grade?:($mixInput['fc']??'-')}}</td></tr><tr><td>Jenis konstruksi</td><td>{{$project->construction_type?:'-'}}</td></tr>
</table>
<h3 class="chapter">1.2 Latar Belakang dan Lingkup Pekerjaan</h3>
<p class="justify">Desain campuran beton diperlukan untuk menentukan perbandingan bahan yang dapat mencapai kuat tekan, kelecakan, keawetan, dan kemudahan pelaksanaan sesuai kebutuhan pekerjaan. Lingkup laporan meliputi identifikasi bahan, pemeriksaan sifat bahan, analisis gradasi, perhitungan proporsi campuran, koreksi kadar air, campuran percobaan, dan evaluasi kuat tekan.</p>{!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title">BAB I<br>DATA DAN INFORMASI PEKERJAAN (LANJUTAN)</h2>
<h3 class="chapter">1.3 Maksud dan Tujuan</h3><ol><li>Memperoleh proporsi semen, air, pasir, dan kerikil untuk satu meter kubik beton.</li><li>Memenuhi mutu beton dan slump rencana sesuai data proyek.</li><li>Menetapkan koreksi bahan berdasarkan kadar air dan penyerapan agregat.</li><li>Menyajikan dasar pemeriksaan dan evaluasi hasil campuran secara terdokumentasi.</li></ol>
<h3 class="chapter">1.4 Lokasi Pekerjaan dan Peta</h3>
<p class="justify">{{$project->location_description?:('Lokasi pekerjaan pengujian berada di '.($project->location_address?:($project->location?:'lokasi yang tercatat pada Data Proyek')).'.')}}@if($project->latitude!==null&&$project->longitude!==null) Lokasi berada pada koordinat {{($project->coordinate_format==='dms')?$dms($project->latitude,true):'Latitude '.number_format((float)$project->latitude,7,'.','')}}, {{($project->coordinate_format==='dms')?$dms($project->longitude,false):'Longitude '.number_format((float)$project->longitude,7,'.','')}}.@endif</p>
@if($project->latitude!==null&&$project->longitude!==null)<table class="info"><tr><td>Latitude</td><td>{{$project->coordinate_format==='dms'?$dms($project->latitude,true):number_format((float)$project->latitude,7,'.','')}}</td></tr><tr><td>Longitude</td><td>{{$project->coordinate_format==='dms'?$dms($project->longitude,false):number_format((float)$project->longitude,7,'.','')}}</td></tr></table>@endif
@if($project->map_image)<h4 class="subchapter" style="text-align:center">PETA LOKASI PEKERJAAN</h4><img class="map-image" src="{{asset('storage/'.$project->map_image)}}" alt="Peta lokasi pekerjaan"><div class="map-caption">{{$project->map_caption?:'Gambar 1. Peta Lokasi Pekerjaan'}}</div>@elseif($project->latitude!==null&&$project->longitude!==null)<div class="notice">Titik lokasi telah tersimpan. Upload gambar peta/site map pada Data Proyek agar peta tercetak pada PDF.</div>@endif
<h3 class="chapter">1.5 Data-data Bahan</h3><table class="data"><tr><th>Bahan</th><th>Nama/Merek</th><th>Produsen/Sumber</th><th>Pemasok</th></tr>
@foreach([['Semen',$cementSource],['Air',$waterSource],['Agregat halus/pasir',$fineSource],['Agregat kasar/kerikil',$coarseSource]] as [$name,$source])<tr><td>{{$name}}</td><td>{{$source?->brand?:($source?->name?:'Belum diisi')}}</td><td>{{$source?->producer?:($source?->quarry?:'Belum diisi')}}</td><td>{{$source?->supplier?:'—'}}</td></tr>@endforeach
</table>
<h3 class="chapter">1.6 Pemeriksaan dan Pengujian di Laboratorium</h3><p class="justify">Pemeriksaan mencakup kadar air, kadar lumpur, berat jenis dan penyerapan, berat isi, analisis saringan, modulus kehalusan, serta keausan agregat kasar. Hasil lengkap disajikan pada lampiran dan digunakan sebagai dasar perhitungan desain campuran.</p>{!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title">BAB II<br>PEMERIKSAAN MATERIAL</h2>
@foreach(['fine'=>'2.1 Agregat Halus / Pasir','coarse'=>'2.2 Agregat Kasar / Kerikil'] as $aggregate=>$title)
<h3 class="chapter">{{$title}}</h3>
@php
$summaryRuns = $aggregateRuns->where('aggregate_type', $aggregate)->groupBy('test_type')->map->last();
@endphp
@if($summaryRuns->isNotEmpty())<table class="data"><tr><th>Pemeriksaan</th><th>Parameter</th><th>Hasil rata-rata</th><th>Status tersimpan</th></tr>
@foreach($summaryRuns as $type=>$run) @forelse(($run->results['averages']??[]) as $key=>$average)<tr><td class="text-left">{{$runLabels[$type]??$pretty($type)}}</td><td class="text-left">{{$pretty($key)}}</td><td>{{$value($average)}}</td><td><b>{{strtoupper(data_get($run->results,'status','-'))}}</b></td></tr>@empty<tr><td class="text-left">{{$runLabels[$type]??$pretty($type)}}</td><td colspan="3">Data observasi belum lengkap.</td></tr>@endforelse @endforeach
</table>@else<p>Belum ada pemeriksaan {{$aggregate==='fine'?'agregat halus':'agregat kasar'}} pada proyek ini.</p>@endif
@endforeach
{!!$footer()!!}</section>

@foreach($reportMixTypes as $reportMixType)
@php
$latestMix=$mixDesigns->where('type',$reportMixType)->last();
$mixInput=$latestMix?->input_data??[];
$mixResult=$latestMix?->result_data??[];
$mixReportTitle=$reportMixType==='mix-design-2012-combined'?'Desain Campuran 2012 (Gradasi Gabungan)':'Desain Campuran 2012';
$mixSectionSuffix=count($reportMixTypes)>1?($loop->iteration===1?'A':'B'):'';
@endphp
<section class="page">{!!$header()!!}<h2 class="section-title">BAB III<br>PERENCANAAN / KOMPOSISI CAMPURAN</h2><h3 class="chapter">3.1{{$mixSectionSuffix}} Lembar Hasil {{$mixReportTitle}}</h3>
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
<div class="two-col"><div><b>Mutu rencana:</b> {{$value($mixInput['fc']??null)}} MPa<br><b>Slump rencana:</b> {{$value($mixInput['slump_design']??null)}} mm<br><b>Ukuran agregat maksimum:</b> {{$value($mixInput['max_size']??null)}} mm</div><div><b>Rasio air-semen:</b> {{$value($mixResult['wc_ratio_calculated']??null)}}@if($reportMixType==='mix-design-2012-combined')<br><b>Pasir optimum (basis agregat SSD):</b> {{$value($mixResult['combined_fine_percent']??null)}} %<br><b>Kerikil optimum (basis agregat SSD):</b> {{$value($mixResult['combined_coarse_percent']??null)}} %@endif</div></div>
@else {!!$missing!!} @endif
{!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title subchapter-title">3.2{{$mixSectionSuffix}} Pemakaian Bahan<br>{{$mixReportTitle}}</h2>
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
<h3 class="chapter">Komposisi Campuran Percobaan untuk Benda Uji Silinder</h3>
<table class="data"><tr><th>Diameter</th><th>Tinggi</th><th>Jumlah silinder</th><th>Volume satu silinder</th><th>Total volume silinder</th><th>Kelebihan</th><th>Volume adukan</th></tr><tr><td class="center">{{$value($mixInput['trial_cylinder_diameter_mm']??null)}} mm</td><td class="center">{{$value($mixInput['trial_cylinder_height_mm']??null)}} mm</td><td class="center">{{$value($mixInput['trial_cylinder_count']??null)}} buah</td><td class="right">{{$value($mixResult['trial_single_cylinder_volume_liter']??null)}} liter</td><td class="right">{{$value($mixResult['trial_volume_liter']??($mixInput['trial_volume_liter']??null))}} liter</td><td class="right">{{$value($mixInput['waste']??null)}} %</td><td class="right">{{$value($mixResult['trial_batch_volume_liter']??null)}} liter</td></tr></table>
<table class="data"><tr><th>Semen</th><th>Air</th><th>Pasir</th><th>Kerikil</th></tr><tr><td class="right">{{$value($mixResult['trial_cement']??null)}} kg</td><td class="right">{{$value($mixResult['trial_water']??null)}} kg</td><td class="right">{{$value($mixResult['trial_fine']??null)}} kg</td><td class="right">{{$value($mixResult['trial_coarse']??null)}} kg</td></tr></table>
@else {!!$missing!!} @endif {!!$footer()!!}</section>

<section class="page">{!!$header()!!}<h2 class="section-title subchapter-title">3.3{{$mixSectionSuffix}} Perhitungan {{$mixReportTitle}}<br>SNI 7656:2012</h2>
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

<section class="page">{!!$header()!!}<h2 class="section-title subchapter-title">3.3{{$mixSectionSuffix}} Perhitungan {{$mixReportTitle}}<br>(Lanjutan)</h2>
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

@endif
@if(in_array($reportPart,['full','chapter-four'],true))
<section class="page page-landscape chapter-four-page">{!!$header()!!}<h2 class="section-title">BAB IV<br>Hasil Pengujian Beton</h2><h3 class="chapter">4.1 Beton Segar / Slump</h3>
@if($slumpActual!==null)<table class="chapter-four-slump"><tr><td><b>Nomor / batch campuran</b><br>{{$latestSlump?->trialMix?->batch_number?:($latestFresh?->number?:'-')}}</td><td><b>Slump beton segar</b><br>{{$value($slumpActual)}} mm</td><td><b>Rentang slump rencana</b><br>{{$value($slumpMin)}} s.d. {{$value($slumpMax)}} mm</td></tr></table>@else<p>Data slump beton segar belum tersedia untuk batch/campuran proyek ini.</p>@endif
<h3 class="chapter">4.2 Benda Uji dan 4.3 Kuat Tekan Beton</h3>
@if($latestStrength)
<p>Nomor pengujian: <b>{{$latestStrength->number}}</b> • Sasaran: {{$value($latestStrength->input_data['target_fc']??null)}} MPa</p>
<table class="chapter-four-layout"><tr><td class="chapter-four-details"><table class="data tiny"><tr><th>No.</th><th>Tanggal buat</th><th>Tanggal uji</th><th>Umur (hari)</th><th>Diameter/Tinggi (mm)</th><th>Berat (kg)</th><th>Beban (kN)</th><th>Aktual (MPa)</th><th>Perkiraan 28 hari (MPa)</th><th>Mutu K (kg/cm²)</th></tr>
@forelse(($latestStrength->result_data['detail_rows']??[]) as $row)<tr><td>{{$row['number']}}</td><td>{{$row['cast_date']}}</td><td>{{$row['test_date']}}</td><td>{{$row['age_days']}}</td><td>{{$value($row['diameter'])}} / {{$value($row['height'])}}</td><td>{{$value($row['weight'])}}</td><td>{{$value($row['load_kn'])}}</td><td>{{$value($row['actual_mpa'])}}</td><td>{{$value($row['estimated_28_mpa'])}}</td><td>{{$value($row['estimated_k_kgcm2'])}}</td></tr>@empty<tr><td colspan="10" class="center">Rincian benda uji belum tersedia.</td></tr>@endforelse
</table></td><td class="chapter-four-summary"><table class="data"><tr><th>Jumlah benda uji</th><td>{{$value($strengthResult['Jumlah benda uji']??null)}}</td></tr><tr><th>Rata-rata perkiraan umur 28 hari</th><td>{{$value($strengthResult['Rata-rata perkiraan 28 hari (MPa)']??null)}} MPa</td></tr><tr><th>Standar deviasi sampel</th><td>{{$value($strengthResult['Standar deviasi sampel (MPa)']??null)}} MPa</td></tr><tr><th>Kuat tekan karakteristik</th><td>{{$value($strengthResult['Kuat tekan karakteristik (MPa)']??null)}} MPa</td></tr><tr><th>Status</th><td><b>{{$strengthResult['Status']??'Belum dievaluasi'}}</b></td></tr></table></td></tr></table>
@else {!!$missing!!} @endif {!!$footer()!!}</section>
@endif

@if(in_array($reportPart,['full','chapter-five'],true))

@php
$fineLegacy=($materialTests['Pemeriksaan Pasir']??collect())->first();
$coarseLegacy=($materialTests['Pemeriksaan Kerikil']??collect())->first();
$average=function($aggregate,$type,$key)use($latestRun){return data_get($latestRun($aggregate,$type)?->results,'averages.'.$key);};
$metric=function($raw,$digits=3)use($value){return $raw===null?'—':$value(round((float)$raw,$digits));};
$conclusionRows=[];
$fineSilt=$average('fine','silt','silt')??$fineLegacy?->silt_content;$coarseSilt=$average('coarse','silt','silt')??$coarseLegacy?->silt_content;
if($fineSilt!==null||$coarseSilt!==null)$conclusionRows[]=['Kadar lumpur / bahan lolos No.200',$metric($fineSilt),$metric($coarseSilt),'%','Acuan umum: pasir ≤ 5%; kerikil ≤ 1%.'];
$fineSieve=$latestRun('fine','sieve');$coarseSieve=$latestRun('coarse','sieve');
if($fineSieve||$coarseSieve)$conclusionRows[]=['Analisa saringan / gradasi',$fineSieve?'Tabel dan grafik terlampir':'—',$coarseSieve?'Tabel dan grafik terlampir':'—','% lolos','Dasar penetapan proporsi dan ukuran agregat.'];
$fineFm=$average('fine','sieve','fineness_modulus')??$fineLegacy?->fineness_modulus;
if($fineFm!==null)$conclusionRows[]=['Modulus kehalusan (FM)',$metric($fineFm),'—','—','Rentang acuan pasir 2,3–3,1.'];
$fineMoisture=$average('fine','moisture','moisture')??$fineLegacy?->field_moisture;$coarseMoisture=$average('coarse','moisture','moisture')??$coarseLegacy?->field_moisture;
if($fineMoisture!==null||$coarseMoisture!==null)$conclusionRows[]=['Kadar air',$metric($fineMoisture),$metric($coarseMoisture),'%','Nilai aktual untuk koreksi air campuran.'];
foreach([['bulk_dry','Berat jenis bulk'],['bulk_ssd','Berat jenis SSD'],['apparent','Berat jenis semu/apparent'],['absorption','Penyerapan air/absorpsi']] as [$key,$label]){
 $fineResult=$average('fine','specific-gravity',$key)??data_get($fineLegacy,match($key){'bulk_dry'=>'bulk_specific_gravity_dry','bulk_ssd'=>'specific_gravity_ssd','apparent'=>'apparent_specific_gravity',default=>'absorption'});
 $coarseResult=$average('coarse','specific-gravity',$key)??data_get($coarseLegacy,match($key){'bulk_dry'=>'bulk_specific_gravity_dry','bulk_ssd'=>'specific_gravity_ssd','apparent'=>'apparent_specific_gravity',default=>'absorption'});
 if($fineResult!==null||$coarseResult!==null)$conclusionRows[]=[$label,$metric($fineResult),$metric($coarseResult),$key==='absorption'?'%':'—',$key==='absorption'?'Untuk koreksi air.':'Data berat jenis hasil pengujian.'];
}
foreach([['loose_bulk_density','Berat isi lepas'],['compacted_bulk_density','Berat isi padat'],['void_percentage','Rongga/void']] as [$key,$label]){
 $fineResult=data_get($fineLegacy,$key);$coarseResult=data_get($coarseLegacy,$key);
 if($fineResult!==null||$coarseResult!==null)$conclusionRows[]=[$label,$metric($fineResult),$metric($coarseResult),$key==='void_percentage'?'%':'kg/m³',$key==='void_percentage'?'Dihitung dari berat isi dan berat jenis.':'Karakteristik agregat untuk proporsi campuran.'];
}
$abrasion=$average('coarse','los-angeles','abrasion')??$coarseLegacy?->abrasion;
if($abrasion!==null)$conclusionRows[]=['Keausan Los Angeles','—',$metric($abrasion),'%','Ketahanan agregat kasar terhadap abrasi.'];
$maximumSize=$coarseLegacy?->nominal_maximum_size??($mixInput['max_size']??null);
if($maximumSize!==null)$conclusionRows[]=['Ukuran maksimum agregat','—',$metric($maximumSize),'mm','Ukuran nominal yang digunakan dalam desain.'];
$shapeValues=collect([['Pipih',$coarseLegacy?->flakiness],['Lonjong',$coarseLegacy?->elongation]])->filter(fn($row)=>$row[1]!==null)->map(fn($row)=>$row[0].' '.$metric($row[1]).'%')->implode('; ');
if($shapeValues!=='')$conclusionRows[]=['Bentuk butiran','—',$shapeValues,'% / visual','Tidak dominan pipih atau panjang.'];
@endphp
<section class="page">{!!$header()!!}<h2 class="section-title">BAB V<br>PENUTUP</h2>
<h3 class="chapter">5.1 Kesimpulan Hasil Pemeriksaan Material</h3>
<table class="data conclusion-table"><tr><th style="width:6%">No.</th><th>Jenis Pemeriksaan</th><th>Pasir / Agregat Halus</th><th>Kerikil / Agregat Kasar</th><th style="width:10%">Satuan</th><th>Keterangan</th></tr>
@foreach($conclusionRows as [$label,$fineConclusion,$coarseConclusion,$unit,$note])<tr><td class="center">{{$loop->iteration}}</td><td><b>{{$label}}</b></td><td class="center">{{$fineConclusion}}</td><td class="center">{{$coarseConclusion}}</td><td class="center">{{$unit}}</td><td>{{$note}}</td></tr>@endforeach
</table>
<h3 class="chapter">5.2 Saran</h3><ol>
<li>Lakukan pengendalian kadar air agregat setiap kali produksi agar jumlah air efektif tetap sesuai desain.</li>
@if($slumpActual!==null)<li>Catat nilai slump setiap batch sebagai data pengendalian konsistensi beton segar.</li>@endif
@if(str_contains(strtolower((string)($strengthResult['Status']??'')),'tidak'))<li>Evaluasi kembali mix design, mutu material, ketelitian batching, pemadatan, curing, dan lakukan pengujian lanjutan karena kuat tekan belum memenuhi sasaran.</li>@elseif($latestStrength)<li>Pertahankan mutu material, ketelitian batching, pemadatan, dan curing agar pencapaian kuat tekan tetap konsisten.</li>@endif
<li>Jika sumber material atau gradasi berubah, lakukan pemeriksaan dan perhitungan ulang sebelum produksi.</li></ol>
{!!$footer()!!}</section>
@endif

@if(in_array($reportPart,['full','approval'],true))
<section class="page approval-page">{!!$header()!!}<h2 class="section-title">LEMBAR PENGESAHAN</h2>
<table class="info"><tr><td>Nama Pekerjaan</td><td>{{$project->name}}</td></tr><tr><td>Nomor Laporan</td><td><b>{{$project->number}}</b></td></tr><tr><td>Pemilik Pekerjaan</td><td>{{$project->owner?:'-'}}</td></tr><tr><td>Tanggal Laporan</td><td>{{$indonesianDate($project->report_date??$signatureDate)}}</td></tr></table>
<p class="justify">Laporan hasil pemeriksaan/pengujian ini telah diperiksa dan disahkan oleh Laboratorium Bahan dan Struktur Program Studi Teknik Sipil.</p>
@if($prefaceApproval)<div class="signature"><span class="signature-date">{{$signatureCity}}, {{$indonesianDate($prefaceApproval->approved_at??$signatureDate)}}</span><br><b>MENYETUJUI</b><br><img class="approval-qr" src="{{$prefaceApproval->qr_data_uri}}" alt="QR persetujuan"><br><b>{{$prefaceApproval->user->name}}</b><br>{{$prefaceApproval->user->position?:$prefaceApproval->user->role}}<br>Ditandatangani secara elektronik</div>@endif
{!!$footer()!!}</section>
@endif

@if(in_array($reportPart,['full','after-approval'],true))
@foreach(['fine'=>'AGREGAT HALUS/PASIR','coarse'=>'AGREGAT KASAR/KERIKIL'] as $aggregate=>$aggregateName)
 @foreach(['moisture','silt','specific-gravity','bulk-density'] as $testType)
 @php
 $run=$latestRun($aggregate,$testType);
 @endphp
 @if($run)<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN {{$aggregate==='fine'?'A':'B'}}.{{$loop->iteration}}<br>{{$runLabels[$testType]}}<br>{{$aggregateName}}</h2>
 <table class="info"><tr><td>Nomor pengujian</td><td><b>{{$run->test_number}}</b></td><td>Nomor sampel</td><td>{{$run->sample_number}}</td></tr><tr><td>Tanggal</td><td>{{$run->tested_at?->format('d/m/Y')}}</td><td>Petugas</td><td>{{$run->technician}}</td></tr></table>
 @php
 $fieldKeys=collect($run->observations??[])->flatMap(fn($o)=>array_keys($o))->reject(fn($k)=>str_starts_with($k,'zone_')||str_ends_with($k,'_sieve_mass')||str_ends_with($k,'_combined_mass'))->unique()->values();
 @endphp
 <table class="data small"><tr><th>Parameter pengamatan</th>@foreach($run->observations??[] as $i=>$obs)<th>Observasi {{$i+1}}</th>@endforeach</tr>
 @foreach($fieldKeys as $key)<tr><td>{{$pretty($key)}}</td>@foreach($run->observations??[] as $obs)<td class="right">{{$value($obs[$key]??null)}}</td>@endforeach</tr>@endforeach</table>
 <h3 class="chapter">Hasil Perhitungan</h3><table class="data"><tr><th>Parameter hasil</th>@foreach(($run->results['observations']??[]) as $obs)<th>Observasi {{$obs['number']??$loop->iteration}}</th>@endforeach<th>Rata-rata</th></tr>
 @forelse(($run->results['averages']??[]) as $key=>$avg)<tr><td>{{$pretty($key)}}</td>@foreach(($run->results['observations']??[]) as $obs)<td class="right">{{$value($obs['values'][$key]??null)}}</td>@endforeach<td class="right soft"><b>{{$value($avg)}}</b></td></tr>@empty<tr><td colspan="5" class="center">Hasil perhitungan belum tersedia.</td></tr>@endforelse</table>
 <p class="small"><b>Metode:</b> {{$run->results['formula']??'Sesuai metode pemeriksaan agregat yang berlaku.'}}</p>
 {!!$footer()!!}</section>@endif
 @endforeach
@endforeach

@foreach(['Pemeriksaan Semen','Pemeriksaan Air'] as $sectionName)
@php
$tests=$materialTests[$sectionName]??collect();
$test=$tests->first();
@endphp
@if($test)<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN<br>{{strtoupper($sectionName)}}</h2><table class="info"><tr><td>Nomor pengujian</td><td><b>{{$test->test_number}}</b></td></tr><tr><td>Nomor sampel</td><td>{{$test->sample_number}}</td></tr><tr><td>Tanggal pengujian</td><td>{{$test->tested_at?->format('d/m/Y')}}</td></tr><tr><td>Petugas</td><td>{{$test->technician}}</td></tr></table>
<table class="data"><tr><th>Parameter</th><th>Hasil</th></tr>@foreach($test->getAttributes() as $key=>$raw)@if(!str_ends_with($key,'_id')&&!in_array($key,['id','test_number','sample_number','tested_at','technician','created_by','updated_by','deleted_at','created_at','updated_at','status'])&&$raw!==null)<tr><td>{{$pretty($key)}}</td><td>{{$value($raw)}}</td></tr>@endif @endforeach</table>
{!!$footer()!!}</section>@endif
@endforeach

@foreach(['fine'=>'AGREGAT HALUS/PASIR','coarse'=>'AGREGAT KASAR/KERIKIL'] as $aggregate=>$aggregateName)
@php
$run=$latestRun($aggregate,'sieve');
$sieveResult=$sieveRows($run,$aggregate);
$sampleMass=$sieveResult[0];
$rows=$sieveResult[1];
@endphp
@if($run)<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN {{$aggregate==='fine'?'A.5':'B.5'}}<br>ANALISIS SARINGAN {{$aggregateName}}</h2>
<p>Nomor: <b>{{$run->test_number}}</b> • Massa sampel: {{$value($sampleMass)}} g</p>
<table class="data small"><tr><th>Saringan</th><th>Ukuran (mm)</th><th>Massa tertahan (g)</th><th>% Tertahan</th><th>% Kumulatif</th><th>% Lolos</th></tr>
@foreach($rows as $row)<tr><td>{{$row['label']}}</td><td class="right">{{$value($row['mm'])}}</td><td class="right">{{$run?$value($row['retained']):'—'}}</td><td class="right">{{$run?$value($row['percent']):'—'}}</td><td class="right">{{$run?$value($row['cumulative']):'—'}}</td><td class="right"><b>{{$run?$value($row['passing']):'—'}}</b></td></tr>@endforeach</table>
{!!$chart($run,$aggregate)!!}<p class="small">Garis merah menunjukkan hasil pengujian; garis berwarna menunjukkan batas bawah dan atas gradasi.</p>{!!$footer()!!}</section>@endif
@endforeach

@php
$run=$latestRun('fine','sieve');
$selectedFineZone=data_get($run?->observations,'0.selected_zone');
$fineZones=$selectedFineZone==='all'?range(1,4):(in_array((int)$selectedFineZone,range(1,4),true)?[(int)$selectedFineZone]:[]);
@endphp
@foreach($fineZones as $zone)
<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN<br>BATAS GRADASI PASIR ZONA {{$zone}}</h2>{!!$chart($run,'fine',$zone)!!}
<table class="data small"><tr><th>Saringan</th><th>Batas bawah (%)</th><th>Batas atas (%)</th></tr>@foreach($sieveInfo['fine'] as [$label,$mm,$key])@if($mm>0)<tr><td>{{$label}} ({{$value($mm)}} mm)</td><td class="right">{{$value($fineLimits[$key][$zone-1][0]??null)}}</td><td class="right">{{$value($fineLimits[$key][$zone-1][1]??null)}}</td></tr>@endif @endforeach</table>{!!$footer()!!}</section>
@endforeach

@php
$run=$latestRun('coarse','sieve');
$coarseNames=[1=>'10 mm',2=>'20 mm',3=>'40 mm'];
$selectedCoarseZone=data_get($run?->observations,'0.selected_zone');
$coarseZones=$selectedCoarseZone==='all'?array_keys($coarseNames):(isset($coarseNames[(int)$selectedCoarseZone])?[(int)$selectedCoarseZone]:[]);
@endphp
@foreach($coarseZones as $zone)
@php
$sizeName = $coarseNames[$zone];
@endphp
<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN<br>BATAS GRADASI KERIKIL MAKSIMUM {{$sizeName}}</h2>{!!$chart($run,'coarse',$zone)!!}
<table class="data small"><tr><th>Saringan</th><th>Batas bawah (%)</th><th>Batas atas (%)</th></tr>@foreach($sieveInfo['coarse'] as [$label,$mm,$key])@if($mm>0)<tr><td>{{$label}} ({{$value($mm)}} mm)</td><td class="right">{{$value($coarseLimits[$key][$zone-1][0]??null)}}</td><td class="right">{{$value($coarseLimits[$key][$zone-1][1]??null)}}</td></tr>@endif @endforeach</table>{!!$footer()!!}</section>
@endforeach

@php
$abrasionRun=$latestRun('coarse','los-angeles');
@endphp
@if($abrasionRun)<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN B.6<br>KEKERASAN/KEAUSAN AGREGAT KASAR<br>MESIN LOS ANGELES</h2>
<table class="info"><tr><td>Nomor pengujian</td><td><b>{{$abrasionRun->test_number}}</b></td></tr><tr><td>Nomor sampel</td><td>{{$abrasionRun->sample_number}}</td></tr><tr><td>Tanggal</td><td>{{$abrasionRun->tested_at?->format('d/m/Y')}}</td></tr></table>
<table class="data"><tr><th>Parameter</th>@foreach($abrasionRun->observations??[] as $i=>$obs)<th>Observasi {{$i+1}}</th>@endforeach</tr>
@php
$abrasionKeys=collect($abrasionRun->observations??[])->flatMap(fn($o)=>array_keys($o))->unique();
@endphp
@foreach($abrasionKeys as $key)<tr><td>{{$pretty($key)}}</td>@foreach($abrasionRun->observations??[] as $obs)<td class="right">{{$value($obs[$key]??null)}}</td>@endforeach</tr>@endforeach
@foreach(($abrasionRun->results['averages']??[]) as $key=>$avg)<tr><th>{{$pretty($key)}} rata-rata</th><th colspan="{{max(1,count($abrasionRun->observations??[]))}}" class="right">{{$value($avg)}}</th></tr>@endforeach</table>
<p><b>Kesimpulan:</b> Nilai keausan hasil pengujian harus dibandingkan dengan persyaratan spesifikasi teknis pekerjaan.</p>
{!!$footer()!!}</section>@endif

@foreach($reportMixTypes as $appendixMixType)
@php $appendixMix=$mixDesigns->where('type',$appendixMixType)->last(); @endphp
@if($appendixMix)
@include('workflows.partials.mix-design-calculation-appendix',['mix'=>$appendixMix,'combined'=>$appendixMixType==='mix-design-2012-combined','appendixLabel'=>'C.'.$loop->iteration])
@endif
@endforeach

@if($slumpActual!==null)<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN D<br>PEMERIKSAAN SLUMP BETON SEGAR</h2><table class="info"><tr><td>Nomor pengujian/batch</td><td>{{$latestSlump?->test_number?:($latestFresh?->number?:'-')}}</td></tr><tr><td>Batch campuran</td><td>{{$latestSlump?->trialMix?->batch_number?:'-'}}</td></tr><tr><td>Slump aktual</td><td><b>{{$value($slumpActual)}} mm</b></td></tr><tr><td>Rentang rencana</td><td>{{$value($slumpMin)}}–{{$value($slumpMax)}} mm</td></tr></table>{!!$footer()!!}</section>@endif
@endif

@if(in_array($reportPart,['full','strength'],true))
@foreach($strengthTests as $strengthTest)
@php
$strengthDetailChunks=collect($strengthTest->result_data['detail_rows']??[])->chunk(10);
if($strengthDetailChunks->isEmpty())$strengthDetailChunks=collect([collect()]);
@endphp
@foreach($strengthDetailChunks as $detailChunk)
<section class="page page-landscape">{!!$header()!!}<h2 class="section-title">LAMPIRAN E<br>PENGUJIAN KUAT TEKAN BETON
@if($strengthTests->count()>1) - {{$strengthTest->number}} @endif
@if($strengthDetailChunks->count()>1) ({{$loop->iteration}}/{{$strengthDetailChunks->count()}}) @endif</h2>
<table class="info"><tr><td>Nomor pengujian</td><td><b>{{$strengthTest->number}}</b></td><td>Tanggal pekerjaan</td><td>{{$strengthTest->work_date?->format('d/m/Y')}}</td><td>Mutu rencana</td><td>{{$value($strengthTest->input_data['target_fc']??null)}} MPa</td></tr></table>
<table class="data strength-landscape-table"><tr><th>No.</th><th>Tanggal Pembuatan</th><th>Tanggal Pengujian</th><th>Umur (hari)</th><th>Diameter (mm)</th><th>Tinggi (mm)</th><th>Berat (kg)</th><th>Beban Maks. (kN)</th><th>Luas (mm²)</th><th>Kuat Tekan Aktual (MPa)</th><th>Faktor Umur</th><th>Perkiraan 28 Hari (MPa)</th><th>Mutu K (kg/cm²)</th></tr>
@forelse($detailChunk as $row)<tr><td>{{$row['number']??$loop->iteration}}</td><td>{{$row['cast_date']??'-'}}</td><td>{{$row['test_date']??'-'}}</td><td>{{$value($row['age_days']??null)}}</td><td>{{$value($row['diameter']??null)}}</td><td>{{$value($row['height']??null)}}</td><td>{{$value($row['weight']??null)}}</td><td>{{$value($row['load_kn']??null)}}</td><td>{{$value($row['area_mm2']??null)}}</td><td><b>{{$value($row['actual_mpa']??null)}}</b></td><td>{{$value($row['age_factor']??null)}}</td><td><b>{{$value($row['estimated_28_mpa']??null)}}</b></td><td><b>{{$value($row['estimated_k_kgcm2']??null)}}</b></td></tr>@empty<tr><td colspan="13">Rincian benda uji belum tersedia.</td></tr>@endforelse</table>{!!$footer()!!}</section>
@endforeach
@endforeach
@endif

@if(in_array($reportPart,['full','after-strength'],true))
@if($documents->isNotEmpty())
@foreach($documents as $module=>$photos)@foreach($photos->chunk(2) as $chunk)
<section class="page">{!!$header()!!}<h2 class="section-title">LAMPIRAN DOKUMENTASI<br>{{\App\Http\Controllers\TestDocumentationController::MODULES[$module]??$pretty($module)}}</h2>
@foreach($chunk as $photo)<div class="photo"><img src="{{asset('storage/'.$photo->photo_path)}}"><b>{{$photo->title}}</b><div class="muted">{{$photo->documented_at?->format('d/m/Y')}} • {{$photo->description}}</div></div>@endforeach {!!$footer()!!}</section>
@endforeach @endforeach
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
@endif
</body>
</html>
