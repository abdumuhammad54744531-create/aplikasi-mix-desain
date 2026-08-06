<?php
namespace App\Services;
use InvalidArgumentException;
final class AggregateTestCalculator {
    public function calculate(string $aggregate,string $test,array $observations):array {
        if(!$observations) throw new InvalidArgumentException('Minimal satu observasi harus diisi.');
        $rows=[];$complete=[];$incomplete=[];foreach($observations as $i=>$o){$v=array_map(fn($x)=>is_numeric($x)?(float)$x:$x,$o);if($this->isIncomplete($aggregate,$test,$v)){$rows[]=['number'=>$i+1,'values'=>[],'incomplete'=>true];$incomplete[]=$i+1;continue;}$row=$this->one($aggregate,$test,$v,$i+1);$rows[]=$row;$complete[]=$row;}
        $averages=[];if($complete){foreach(array_keys($complete[0]['values']) as $key){$nums=array_column(array_column($complete,'values'),$key);$averages[$key]=array_sum($nums)/count($nums);}}
        return ['observations'=>$rows,'averages'=>$averages,'formula'=>$complete[0]['formula']??'Perhitungan menunggu data observasi dilengkapi.','valid'=>count($incomplete)===0,'incomplete_observations'=>$incomplete];
    }
    private function isIncomplete(string $aggregate,string $test,array $values):bool {
        $keys=match($test){'moisture'=>['container','wet_container','dry_container'],'silt'=>['dry_before','dry_after'],'specific-gravity'=>$aggregate==='fine'?['oven_dry','pyc_water','pyc_sample_water','ssd']:['oven_dry','ssd','submerged'],'bulk-density'=>['container','full_container','volume','specific_gravity'],'sieve'=>$aggregate==='fine'?['sample_mass','r095','r475','r236','r118','r060','r030','r015','pan']:['sample_mass','r750','r375','r190','r095','r475','pan'],'los-angeles'=>['initial','retained'],default=>[]};
        foreach($keys as $key)if(!array_key_exists($key,$values)||$values[$key]===null||$values[$key]==='')return true;
        return $test==='sieve'&&(float)$values['sample_mass']<=0;
    }
    private function one(string $a,string $t,array $v,int $n):array {
        $positive=function(array $keys)use($v){foreach($keys as $k)if(!isset($v[$k])||$v[$k]<0)throw new InvalidArgumentException("Observasi memiliki nilai kosong atau negatif.");};
        if($t==='moisture'){ $positive(['container','wet_container','dry_container']); $den=$v['dry_container']-$v['container']; if($den<=0)throw new InvalidArgumentException("Massa kering harus lebih besar dari massa wadah.");
            return ['number'=>$n,'values'=>['moisture'=>($v['wet_container']-$v['dry_container'])/$den*100],'formula'=>'A = wadah; B = wadah + benda uji basah; C = wadah + benda uji kering; D = B − A; E = C − A; Kadar air = (D − E) / E × 100%'];}
        if($t==='silt'){ $positive(['dry_before','dry_after']); if($v['dry_before']<=0||$v['dry_after']>$v['dry_before'])throw new InvalidArgumentException('Massa awal harus lebih dari nol dan tidak boleh lebih kecil dari massa setelah pencucian.');
            return ['number'=>$n,'values'=>['silt'=>($v['dry_before']-$v['dry_after'])/$v['dry_before']*100],'formula'=>'A = massa kering sebelum pencucian; B = massa kering setelah pencucian; C = A − B; Kadar lumpur = C / A × 100%'];}
        if($t==='specific-gravity' && $a==='fine'){ $positive(['ssd','pyc_water','pyc_sample_water','oven_dry']); $d=$v['pyc_water']+$v['ssd']-$v['pyc_sample_water']; if($d<=0||$v['oven_dry']<=0)throw new InvalidArgumentException('Kombinasi massa piknometer tidak valid.');
            return ['number'=>$n,'values'=>['bulk_dry'=>$v['oven_dry']/$d,'bulk_ssd'=>$v['ssd']/$d,'apparent'=>$v['oven_dry']/($v['pyc_water']+$v['oven_dry']-$v['pyc_sample_water']),'absorption'=>($v['ssd']-$v['oven_dry'])/$v['oven_dry']*100],'formula'=>'A = kering oven; B = piknometer + air; C = piknometer + benda uji + air; D = SSD; E = B + D − C; BJ kering = A/E; BJ SSD = D/E; BJ semu = A/(B+A−C); Penyerapan = (D−A)/A × 100%'];}
        if($t==='specific-gravity'){ $positive(['oven_dry','ssd','submerged']); $d=$v['ssd']-$v['submerged']; if($d<=0||$v['oven_dry']<=0)throw new InvalidArgumentException('Massa SSD harus lebih besar dari massa dalam air.');
            return ['number'=>$n,'values'=>['bulk_dry'=>$v['oven_dry']/$d,'bulk_ssd'=>$v['ssd']/$d,'apparent'=>$v['oven_dry']/($v['oven_dry']-$v['submerged']),'absorption'=>($v['ssd']-$v['oven_dry'])/$v['oven_dry']*100],'formula'=>'A = kering oven; B = SSD di udara; C = dalam air; D = B − C; BJ kering = A/D; BJ SSD = B/D; BJ semu = A/(A−C); Penyerapan = (B−A)/A × 100%'];}
        if($t==='bulk-density'){ $positive(['container','full_container','volume','specific_gravity']); $mass=$v['full_container']-$v['container']; if($mass<=0||$v['volume']<=0||$v['specific_gravity']<=0)throw new InvalidArgumentException('Massa agregat, volume, dan berat jenis harus lebih dari nol.'); $density=$mass/($v['volume']/1000000);
            return ['number'=>$n,'values'=>['bulk_density'=>$density,'voids'=>($v['specific_gravity']*1000-$density)/($v['specific_gravity']*1000)*100],'formula'=>'A = massa bejana; B = bejana + agregat; C = volume; D = berat jenis; E = B − A; Berat isi = E/(C/1.000.000); Rongga = (D×1.000−BI)/(D×1.000) × 100%'];}
        if($t==='sieve'){ $keys=$a==='fine'?['r095','r475','r236','r118','r060','r030','r015','pan']:['r750','r375','r190','r095','r475','pan']; $positive(array_merge(['sample_mass'],$keys)); if($v['sample_mass']<=0)throw new InvalidArgumentException('Massa sampel harus lebih dari nol.'); $sum=array_sum(array_map(fn($k)=>$v[$k],$keys)); $cum=0;$cumPct=[]; foreach($keys as $k){$cum+=$v[$k];$cumPct[$k]=$cum/$v['sample_mass']*100;} $fm=$a==='fine'?array_sum(array_map(fn($key)=>$cumPct[$key],['r475','r236','r118','r060','r030','r015']))/100:0;
            return ['number'=>$n,'values'=>['mass_total'=>$sum,'mass_difference'=>$v['sample_mass']-$sum,'fineness_modulus'=>$fm],'sieve_cumulative'=>$cumPct,'formula'=>'% tertahan kumulatif = massa kumulatif / massa sampel × 100%; FM = jumlah % kumulatif saringan standar / 100'];}
        if($t==='los-angeles'){ $positive(['initial','retained']); if($v['initial']<=0||$v['retained']>$v['initial'])throw new InvalidArgumentException('Massa awal harus lebih dari nol dan tidak boleh lebih kecil dari massa tertahan.');
            return ['number'=>$n,'values'=>['abrasion'=>($v['initial']-$v['retained'])/$v['initial']*100],'formula'=>'A = massa awal; B = massa tertahan setelah pengujian; C = A − B; Keausan Los Angeles = C / A × 100%'];}
        throw new InvalidArgumentException('Jenis pengujian belum didukung.');
    }
}
