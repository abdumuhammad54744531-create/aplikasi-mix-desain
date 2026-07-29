<?php
namespace App\Services\MixDesign;
use InvalidArgumentException;
final class CompressiveStrengthCalculator {
    public function calculate(float $load, string $loadUnit, float $area): array {
        if ($load <= 0 || $area <= 0) throw new InvalidArgumentException('Beban maksimum dan luas penampang harus lebih dari nol.');
        $newton=$loadUnit==='kN'?$load*1000:$load; $result=$newton/$area;
        return ['result'=>$result,'formula'=>"f'c = P / A",'substitution'=>"{$newton} N / {$area} mm²",'unit'=>'MPa','valid'=>true,'message'=>'Kuat tekan berhasil dihitung.'];
    }
}
