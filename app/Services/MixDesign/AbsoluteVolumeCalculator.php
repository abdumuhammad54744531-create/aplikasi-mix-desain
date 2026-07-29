<?php
namespace App\Services\MixDesign;
use InvalidArgumentException;
final class AbsoluteVolumeCalculator {
    public function material(float $mass, float $specificGravity, float $waterDensity=1000): array {
        if ($mass < 0 || $specificGravity <= 0 || $waterDensity <= 0) throw new InvalidArgumentException('Massa tidak boleh negatif dan berat jenis harus lebih dari nol.');
        $result=$mass/($specificGravity*$waterDensity);
        return ['result'=>$result,'formula'=>'V = W / (SG × ρ air)','substitution'=>"{$mass} / ({$specificGravity} × {$waterDensity})",'unit'=>'m³','valid'=>true,'message'=>'Volume absolut berhasil dihitung.'];
    }
}
