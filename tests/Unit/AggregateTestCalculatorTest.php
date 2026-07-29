<?php
namespace Tests\Unit;
use App\Services\AggregateTestCalculator;
use PHPUnit\Framework\TestCase;
class AggregateTestCalculatorTest extends TestCase {
 public function test_kadar_air_multi_observasi_dan_rata_rata():void {
  $r=(new AggregateTestCalculator)->calculate('fine','moisture',[
   ['container'=>0,'wet_container'=>1000,'dry_container'=>946.5],
   ['container'=>0,'wet_container'=>1000,'dry_container'=>938.0],
  ]);
  $this->assertCount(2,$r['observations']); $this->assertEqualsWithDelta(5.65,$r['observations'][0]['values']['moisture'],.01); $this->assertArrayHasKey('moisture',$r['averages']);
 }
 public function test_berat_jenis_agregat_kasar():void {
  $r=(new AggregateTestCalculator)->calculate('coarse','specific-gravity',[['oven_dry'=>1000,'ssd'=>1020,'submerged'=>620]]);
  $this->assertEqualsWithDelta(2.5,$r['averages']['bulk_dry'],.001); $this->assertEqualsWithDelta(2.0,$r['averages']['absorption'],.001);
 }
 public function test_los_angeles():void {
  $r=(new AggregateTestCalculator)->calculate('coarse','los-angeles',[['initial'=>5000,'retained'=>3800]]);
  $this->assertSame(24.0,$r['averages']['abrasion']);
 }
 public function test_massa_awal_saringan_sama_dengan_total_massa_tertahan():void {
  $r=(new AggregateTestCalculator)->calculate('fine','sieve',[[
   'sample_mass'=>1000,'r475'=>50,'r236'=>100,'r118'=>150,'r060'=>200,'r030'=>200,'r015'=>200,'pan'=>100,
  ]]);
  $this->assertSame(1000.0,$r['averages']['mass_total']);
  $this->assertSame(0.0,$r['averages']['mass_difference']);
 }
}
