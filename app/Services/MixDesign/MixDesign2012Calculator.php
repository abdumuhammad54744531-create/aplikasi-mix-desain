<?php
namespace App\Services\MixDesign;
use InvalidArgumentException;
final class MixDesign2012Calculator {
 public function calculate(array $v):array {
  foreach(['cement_sg','coarse_sg','fine_sg','fc','sd','deviation_factor','water','wc_ratio','coarse_density','fine_fm','fresh_density','max_size'] as $k)if(!isset($v[$k])||$v[$k]<=0)throw new InvalidArgumentException("Nilai {$k} harus lebih dari nol.");
  $margin=($v['sd']+($v['sd_additional']??0))*$v['deviation_factor'];$fcr=$v['fc']+$margin;
  $strengths=[15,20,25,30,35,40];$fasValues=((int)($v['air_entrained']??0)===1)?[.70,.60,.52,.45,.39,.35]:[.79,.69,.61,.54,.47,.42];
  if($fcr<=$strengths[0])$wc=$fasValues[0];elseif($fcr>=$strengths[count($strengths)-1])$wc=$fasValues[count($fasValues)-1];else{for($i=0;$i<count($strengths)-1;$i++)if($fcr>=$strengths[$i]&&$fcr<=$strengths[$i+1]){$wc=$fasValues[$i]+($fcr-$strengths[$i])/($strengths[$i+1]-$strengths[$i])*($fasValues[$i+1]-$fasValues[$i]);break;}}
  $v['wc_ratio']=$wc;$cement=$v['water']/$wc;
  if(isset($v['smallest_form_dimension'],$v['slab_thickness'],$v['clear_rebar_spacing'])){$limit=min($v['smallest_form_dimension']/5,$v['slab_thickness']/3,$v['clear_rebar_spacing']*.75);if(($v['tested_max_size']??0)>0)$limit=min($limit,$v['tested_max_size']);$eligible=array_values(array_filter([9.5,12.7,19,25,37.5,50,75,150],fn($x)=>$x<=$limit+.0001));$v['max_size']=$eligible?end($eligible):9.5;}
  $volWater=$v['water']/1000;$volCement=$cement/($v['cement_sg']*1000);$volAir=($v['air_content']??0)/100;$aggregateMassAvailable=$v['fresh_density']-$v['water']-$cement;if($aggregateMassAvailable<=0)throw new InvalidArgumentException('Berat beton segar harus lebih besar daripada jumlah berat semen dan air.');
  if(($v['combined_mode']??0)>0){
   $finePercent=(float)($v['combined_fine_percent']??0);if($finePercent<0||$finePercent>100)throw new InvalidArgumentException('Persentase pasir gradasi gabungan harus antara 0 dan 100%.');
   $coarsePercent=100-$finePercent;$fineMass=$aggregateMassAvailable*$finePercent/100;$coarse=$aggregateMassAvailable*$coarsePercent/100;
   $fineAbsolute=$fineMass;$volCoarse=$coarse/($v['coarse_sg']*1000);$volFine=$fineAbsolute/($v['fine_sg']*1000);$coarseRatio=$volCoarse;
  }else{
   $base=match((string)$v['max_size']){'9.5'=>'0.74','12.7'=>'0.83','19'=>'0.90','25'=>'0.95','37.5'=>'0.99','50'=>'1.02','75'=>'1.06','150'=>'1.11',default=>null};
   if($base===null)throw new InvalidArgumentException('Ukuran agregat maksimum tidak tersedia.');
   $coarseRatio=(float)$base-.1*min($v['fine_fm'],3);$coarse=$v['coarse_density']*$coarseRatio;$fineMass=$v['fresh_density']-$v['water']-$cement-$coarse;
   $volCoarse=$coarse/($v['coarse_sg']*1000);$volFine=1-$volWater-$volCement-$volCoarse-$volAir;if($volFine<=0)throw new InvalidArgumentException('Jumlah volume bahan melebihi 1 m³.');
   $fineAbsolute=$volFine*$v['fine_sg']*1000;
  }
  $fineFree=($v['fine_moisture']-$v['fine_absorption'])/100*$fineAbsolute;$coarseFree=($v['coarse_moisture']-$v['coarse_absorption'])/100*$coarse;
  $fineField=$fineAbsolute*(1+$v['fine_moisture']/100)/(1+$v['fine_absorption']/100);$coarseField=$coarse*(1+$v['coarse_moisture']/100)/(1+$v['coarse_absorption']/100);
  $waterAdded=max(0,$v['water']-$fineFree-$coarseFree);$total=$cement+$fineField+$coarseField+$waterAdded;
  $hasCylinder=isset($v['trial_cylinder_diameter_mm'],$v['trial_cylinder_height_mm'],$v['trial_cylinder_count']);$singleCylinderVolume=null;
  if($hasCylinder){$diameter=(float)$v['trial_cylinder_diameter_mm'];$height=(float)$v['trial_cylinder_height_mm'];$cylinderCount=(int)$v['trial_cylinder_count'];if($diameter<=0||$height<=0||$cylinderCount<1)throw new InvalidArgumentException('Diameter, tinggi, dan jumlah silinder harus lebih dari nol.');$singleCylinderVolume=pi()/4*($diameter/1000)**2*($height/1000)*1000;$trialVolume=$singleCylinderVolume*$cylinderCount;}else{$cylinderCount=null;$trialVolume=(float)($v['trial_volume_liter']??20);}
  $trialBatchVolume=$trialVolume*(1+($v['waste']??0)/100);$trialFactor=$trialBatchVolume/1000;
  $result=['margin'=>$margin,'fcr'=>$fcr,'wc_ratio_calculated'=>$wc,'coarse_ratio'=>$coarseRatio,'cement'=>$cement,'aggregate_mass_available'=>$aggregateMassAvailable,'coarse_ssd'=>$coarse,'fine_mass_method'=>$fineMass,
   'fine_ssd'=>$fineAbsolute,'vol_water'=>$volWater,'vol_cement'=>$volCement,'vol_coarse'=>$volCoarse,'vol_fine'=>$volFine,'vol_air'=>$volAir,
   'fine_free_water'=>$fineFree,'coarse_free_water'=>$coarseFree,'fine_field'=>$fineField,'coarse_field'=>$coarseField,'water_added'=>$waterAdded,
   'total_fresh_mass'=>$total,'ratio_cement'=>1,'ratio_fine'=>$fineField/$cement,'ratio_coarse'=>$coarseField/$cement,'ratio_water'=>$waterAdded/$cement,
   'trial_single_cylinder_volume_liter'=>$singleCylinderVolume,'trial_cylinder_count'=>$cylinderCount,'trial_volume_liter'=>$trialVolume,'trial_batch_volume_liter'=>$trialBatchVolume,
   'trial_cement'=>$cement*$trialFactor,'trial_fine'=>$fineField*$trialFactor,'trial_coarse'=>$coarseField*$trialFactor,'trial_water'=>$waterAdded*$trialFactor,
   'sacks_per_m3'=>$cement/50,'fine_per_sack'=>$fineField/($cement/50),'coarse_per_sack'=>$coarseField/($cement/50),'water_per_sack'=>$waterAdded/($cement/50)];
  if(($v['combined_mode']??0)>0)$result+=['combined_fine_percent'=>$v['combined_fine_percent'],'combined_coarse_percent'=>100-$v['combined_fine_percent'],'combined_total_percent'=>100,'combined_deviation'=>$v['combined_deviation']??null,'gradation_max_size'=>$v['gradation_max_size']??null,'gradation_curve'=>$v['gradation_curve']??null];
  return $result;
 }
}
