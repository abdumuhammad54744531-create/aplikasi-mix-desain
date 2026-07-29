<?php
namespace App\Services\MixDesign;
use InvalidArgumentException;
final class MoistureCorrectionCalculator {
    public function calculate(float $ssdMass, float $absorption, float $moisture): array {
        if ($ssdMass < 0 || $absorption < 0 || $moisture < 0) throw new InvalidArgumentException('Massa, penyerapan, dan kadar air tidak boleh negatif.');
        $dry=$ssdMass/(1+$absorption/100); $wet=$dry*(1+$moisture/100); $freeWater=$dry*($moisture-$absorption)/100;
        return ['field_mass'=>$wet,'free_water'=>$freeWater,'formula'=>'Wbasah = Wkering × (1 + MC); Air bebas = Wkering × (MC − Abs)',
            'unit'=>'kg','valid'=>true,'message'=>$freeWater>0?'Agregat menyumbangkan air.':($freeWater<0?'Agregat menyerap air.':'Agregat dalam kondisi SSD.')];
    }
}
