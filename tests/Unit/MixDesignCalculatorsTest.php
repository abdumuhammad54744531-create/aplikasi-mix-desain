<?php
namespace Tests\Unit;
use App\Services\MixDesign\{AbsoluteVolumeCalculator,CompressiveStrengthCalculator,InterpolationService,MixDesign2012Calculator,MoistureCorrectionCalculator};
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
class MixDesignCalculatorsTest extends TestCase {
    public function test_interpolasi_linier():void { $r=(new InterpolationService)->calculate(15,10,20,20,30); $this->assertSame(25.0,$r['result']); $this->assertTrue($r['valid']); }
    public function test_interpolasi_menolak_pembagian_nol():void { $this->expectException(InvalidArgumentException::class); (new InterpolationService)->calculate(1,1,2,1,3); }
    public function test_volume_absolut():void { $r=(new AbsoluteVolumeCalculator)->material(315,3.15); $this->assertEqualsWithDelta(.1,$r['result'],.000001); }
    public function test_agregat_basah_menyumbang_air():void { $r=(new MoistureCorrectionCalculator)->calculate(800,2,5); $this->assertGreaterThan(0,$r['free_water']); }
    public function test_agregat_kering_menyerap_air():void { $r=(new MoistureCorrectionCalculator)->calculate(800,5,2); $this->assertLessThan(0,$r['free_water']); }
    public function test_kondisi_ssd_tidak_mengoreksi_air():void { $r=(new MoistureCorrectionCalculator)->calculate(800,3,3); $this->assertEqualsWithDelta(0,$r['free_water'],.000001); }
    public function test_kuat_tekan_kn_ke_mpa():void { $r=(new CompressiveStrengthCalculator)->calculate(450,'kN',22500); $this->assertSame(20.0,$r['result']); }
    public function test_beban_nol_ditolak():void { $this->expectException(InvalidArgumentException::class); (new CompressiveStrengthCalculator)->calculate(0,'kN',22500); }
    public function test_mix_design_2012_menghasilkan_komposisi_lapangan():void {
        $r=(new MixDesign2012Calculator)->calculate([
            'cement_sg'=>3.15,'coarse_sg'=>2.67,'fine_sg'=>2.61,'fc'=>25,'sd'=>7.5,
            'sd_additional'=>0,'deviation_factor'=>1.64,'water'=>193,'wc_ratio'=>.45,
            'coarse_density'=>1560,'fine_fm'=>2.74,'fresh_density'=>2380,'max_size'=>25,
            'air_content'=>1.5,'fine_moisture'=>5.75,'fine_absorption'=>2.35,
            'coarse_moisture'=>2.4,'coarse_absorption'=>1.85,'trial_volume_liter'=>20,'waste'=>15,
        ]);
        $this->assertEqualsWithDelta(.447,$r['wc_ratio_calculated'],.000001);
        $this->assertEqualsWithDelta(431.767,$r['cement'],.001);
        $this->assertEqualsWithDelta(37.3,$r['fcr'],.001);
        $this->assertGreaterThan(0,$r['fine_field']);
        $this->assertGreaterThan(0,$r['coarse_field']);
        $this->assertGreaterThan(0,$r['trial_cement']);
    }
    public function test_gradasi_gabungan_membagi_berat_beton_tersisa_dan_menghitung_volume_agregat():void {
        $input=[
            'cement_sg'=>3.15,'coarse_sg'=>2.67,'fine_sg'=>2.61,'fc'=>25,'sd'=>7.5,
            'sd_additional'=>0,'deviation_factor'=>1.64,'water'=>193,'wc_ratio'=>.45,
            'coarse_density'=>1560,'fine_fm'=>2.74,'fresh_density'=>2380,'max_size'=>25,
            'air_content'=>1.5,'fine_moisture'=>5.75,'fine_absorption'=>2.35,
            'coarse_moisture'=>2.4,'coarse_absorption'=>1.85,'trial_volume_liter'=>20,'waste'=>15,
            'combined_mode'=>1,'combined_fine_percent'=>38.5,'combined_deviation'=>2.4,
            'gradation_max_size'=>20,'gradation_curve'=>5,
        ];
        $r=(new MixDesign2012Calculator)->calculate($input);
        $available=$input['fresh_density']-$input['water']-$r['cement'];

        $this->assertEqualsWithDelta($available,$r['aggregate_mass_available'],.000001);
        $this->assertEqualsWithDelta($available*.615,$r['coarse_ssd'],.000001);
        $this->assertEqualsWithDelta($available*.385,$r['fine_mass_method'],.000001);
        $this->assertEqualsWithDelta($r['coarse_ssd']/($input['coarse_sg']*1000),$r['vol_coarse'],.000001);
        $this->assertEqualsWithDelta($r['fine_mass_method']/($input['fine_sg']*1000),$r['vol_fine'],.000001);
    }
}
