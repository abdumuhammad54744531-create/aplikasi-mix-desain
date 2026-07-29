<?php
namespace App\Services\MixDesign;
use InvalidArgumentException;
final class InterpolationService {
    public function calculate(float $x, float $x1, float $y1, float $x2, float $y2): array {
        if ($x2 === $x1) throw new InvalidArgumentException('Interpolasi gagal: nilai x1 dan x2 tidak boleh sama.');
        $outside = $x < min($x1,$x2) || $x > max($x1,$x2);
        $y = $y1 + (($x-$x1)/($x2-$x1))*($y2-$y1);
        return ['result'=>$y,'formula'=>'y = y1 + ((x - x1) / (x2 - x1)) × (y2 - y1)',
            'substitution'=>"{$y1} + (({$x} - {$x1}) / ({$x2} - {$x1})) × ({$y2} - {$y1})",
            'unit'=>null,'valid'=>!$outside,'message'=>$outside?'Nilai berada di luar rentang referensi.':'Interpolasi berhasil.'];
    }
}
