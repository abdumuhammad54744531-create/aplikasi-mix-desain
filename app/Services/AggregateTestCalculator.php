<?php
namespace App\Services;
use InvalidArgumentException;
final class AggregateTestCalculator {
    public function calculate(string $aggregate,string $test,array $observations):array {
        if(!$observations) throw new InvalidArgumentException('Minimal satu observasi harus diisi.');
        $rows=[]; foreach($observations as $i=>$o){ $v=array_map(fn($x)=>is_numeric($x)?(float)$x:$x,$o); $rows[]=$this->one($aggregate,$test,$v,$i+1); }
        $keys=array_keys($rows[0]['values']); $averages=[];
        foreach($keys as $key){$nums=array_column(array_column($rows,'values'),$key); $averages[$key]=array_sum($nums)/count($nums);}
        return ['observations'=>$rows,'averages'=>$averages,'formula'=>$rows[0]['formula'],'valid'=>true];
    }
    private function one(string $a,string $t,array $v,int $n):array {
        $positive=function(array $keys)use($v){foreach($keys as $k)if(!isset($v[$k])||$v[$k]<0)throw new InvalidArgumentException("Observasi memiliki nilai kosong atau negatif.");};
        if($t==='moisture'){ $positive(['container','wet_container','dry_container']); $den=$v['dry_container']-$v['container']; if($den<=0)throw new InvalidArgumentException("Massa kering harus lebih besar dari massa wadah.");
            return ['number'=>$n,'values'=>['moisture'=>($v['wet_container']-$v['dry_container'])/$den*100],'formula'=>'Kadar air = (massa sebelum oven - massa setelah oven) / massa benda uji kering × 100%'];}
        if($t==='silt'){ $positive(['dry_before','dry_after']); if($v['dry_before']<=0||$v['dry_after']>$v['dry_before'])throw new InvalidArgumentException('Massa awal harus lebih dari nol dan tidak boleh lebih kecil dari massa setelah pencucian.');
            return ['number'=>$n,'values'=>['silt'=>($v['dry_before']-$v['dry_after'])/$v['dry_before']*100],'formula'=>'Kadar lumpur = (massa awal - massa setelah pencucian) / massa awal × 100%'];}
        if($t==='specific-gravity' && $a==='fine'){ $positive(['ssd','pyc_water','pyc_sample_water','oven_dry']); $d=$v['pyc_water']+$v['ssd']-$v['pyc_sample_water']; if($d<=0||$v['oven_dry']<=0)throw new InvalidArgumentException('Kombinasi massa piknometer tidak valid.');
            return ['number'=>$n,'values'=>['bulk_dry'=>$v['oven_dry']/$d,'bulk_ssd'=>$v['ssd']/$d,'apparent'=>$v['oven_dry']/($v['pyc_water']+$v['oven_dry']-$v['pyc_sample_water']),'absorption'=>($v['ssd']-$v['oven_dry'])/$v['oven_dry']*100],'formula'=>'SNI 1970:2016 - berat jenis curah kering, SSD, semu, dan penyerapan dari data piknometer'];}
        if($t==='specific-gravity'){ $positive(['oven_dry','ssd','submerged']); $d=$v['ssd']-$v['submerged']; if($d<=0||$v['oven_dry']<=0)throw new InvalidArgumentException('Massa SSD harus lebih besar dari massa dalam air.');
            return ['number'=>$n,'values'=>['bulk_dry'=>$v['oven_dry']/$d,'bulk_ssd'=>$v['ssd']/$d,'apparent'=>$v['oven_dry']/($v['oven_dry']-$v['submerged']),'absorption'=>($v['ssd']-$v['oven_dry'])/$v['oven_dry']*100],'formula'=>'SNI 1969:2016 - berat jenis curah kering, SSD, semu, dan penyerapan'];}
        if($t==='bulk-density'){ $positive(['container','full_container','volume','specific_gravity']); $mass=$v['full_container']-$v['container']; if($mass<=0||$v['volume']<=0||$v['specific_gravity']<=0)throw new InvalidArgumentException('Massa agregat, volume, dan berat jenis harus lebih dari nol.'); $density=$mass/($v['volume']/1000000);
            return ['number'=>$n,'values'=>['bulk_density'=>$density,'voids'=>($v['specific_gravity']*1000-$density)/($v['specific_gravity']*1000)*100],'formula'=>'Berat isi = massa agregat / volume bejana; Rongga = (SG×ρair - berat isi)/(SG×ρair) × 100%'];}
        if($t==='sieve'){ $keys=$a==='fine'?['r475','r236','r118','r060','r030','r015','pan']:['r750','r375','r190','r095','r475','pan']; $positive(array_merge(['sample_mass'],$keys)); if($v['sample_mass']<=0)throw new InvalidArgumentException('Massa sampel harus lebih dari nol.'); $sum=array_sum(array_map(fn($k)=>$v[$k],$keys)); $cum=0;$cumPct=[]; foreach($keys as $k){$cum+=$v[$k];$cumPct[$k]=$cum/$v['sample_mass']*100;} $fm=$a==='fine'?array_sum(array_slice(array_values($cumPct),0,6))/100:0;
            return ['number'=>$n,'values'=>['mass_total'=>$sum,'mass_difference'=>$v['sample_mass']-$sum,'fineness_modulus'=>$fm],'sieve_cumulative'=>$cumPct,'formula'=>'% tertahan kumulatif = massa kumulatif / massa sampel × 100%; FM = jumlah % kumulatif saringan standar / 100'];}
        if($t==='los-angeles'){ $positive(['initial','retained']); if($v['initial']<=0||$v['retained']>$v['initial'])throw new InvalidArgumentException('Massa awal harus lebih dari nol dan tidak boleh lebih kecil dari massa tertahan.');
            return ['number'=>$n,'values'=>['abrasion'=>($v['initial']-$v['retained'])/$v['initial']*100],'formula'=>'Keausan Los Angeles = (massa awal - massa tertahan) / massa awal × 100%'];}
        throw new InvalidArgumentException('Jenis pengujian belum didukung.');
    }
}
