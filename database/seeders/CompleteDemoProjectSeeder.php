<?php

namespace Database\Seeders;

use App\Models\{AggregateTestRun,CementTest,CoarseAggregateTest,FineAggregateTest,MaterialSource,MixDesign,Project,User,WaterTest};
use App\Services\AggregateTestCalculator;
use Illuminate\Database\Seeder;

class CompleteDemoProjectSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $project = Project::updateOrCreate(['number' => 'DEMO-001'], [
            'name' => 'DATA CONTOH - Pembangunan Gedung Laboratorium',
            'work_package' => 'Pekerjaan Struktur Beton Bertulang',
            'owner' => 'Instansi Contoh', 'contractor' => 'PT Konstruksi Contoh',
            'consultant' => 'Konsultan Pengawas Contoh', 'location' => 'Kota Baubau, Sulawesi Tenggara',
            'contract_number' => 'KONTRAK-DEMO/001/2026', 'contract_date' => '2026-07-01',
            'start_date' => '2026-07-05', 'end_date' => '2026-12-31',
            'person_in_charge' => 'Penanggung Jawab Contoh', 'supervisor' => 'Pengawas Contoh',
            'concrete_grade' => "f'c 25 MPa", 'construction_type' => 'Gedung bertingkat',
            'environment' => 'Lingkungan normal', 'notes' => 'DATA CONTOH untuk demonstrasi aplikasi. Bukan hasil pengujian resmi.',
            'status' => 'aktif', 'created_by' => $admin->id, 'updated_by' => $admin->id,
        ]);

        $materials = [];
        foreach ([
            ['SEM-DEMO','Semen','Semen PCC Contoh','PCC','Pabrik Contoh',null,'Supplier Contoh'],
            ['AIR-DEMO','Air','Air Bersih Laboratorium',null,null,'Instalasi laboratorium',null],
            ['PAS-DEMO','Pasir','Pasir Sungai Contoh',null,null,'Quarry Pasir Contoh','Supplier Agregat Contoh'],
            ['KER-DEMO','Batu pecah','Batu Pecah 20 mm Contoh',null,null,'Quarry Batu Contoh','Supplier Agregat Contoh'],
        ] as [$code,$type,$name,$brand,$producer,$quarry,$supplier]) {
            $materials[$code] = MaterialSource::updateOrCreate(['code'=>$code],[
                'project_id'=>$project->id,'type'=>$type,'name'=>$name,'brand'=>$brand,'producer'=>$producer,
                'quarry'=>$quarry,'supplier'=>$supplier,'sampled_at'=>'2026-07-15','sample_number'=>"SPL-{$code}",
                'condition'=>'Baik - data contoh','notes'=>'Data demonstrasi, bukan data resmi.',
                'created_by'=>$admin->id,'updated_by'=>$admin->id,
            ]);
        }

        CementTest::updateOrCreate(['test_number'=>'SEM-DEMO-001'],[
            'project_id'=>$project->id,'material_source_id'=>$materials['SEM-DEMO']->id,'sample_number'=>'SPL-SEM-DEMO',
            'received_at'=>'2026-07-15','tested_at'=>'2026-07-16','technician'=>'Teknisi Demo','status'=>'draft',
            'cement_type'=>'PCC','brand'=>'Merek Contoh','batch_number'=>'BATCH-DEMO-01','color'=>'Abu-abu',
            'package_condition'=>'Baik','has_lumps'=>false,'specific_gravity'=>3.05,'fineness'=>4.2,
            'normal_consistency'=>26.5,'initial_setting_time'=>125,'final_setting_time'=>245,
            'mortar_strength'=>31.8,'temperature'=>27.5,'notes'=>'DATA CONTOH - belum untuk laporan resmi.',
            'created_by'=>$admin->id,'updated_by'=>$admin->id,
        ]);
        WaterTest::updateOrCreate(['test_number'=>'AIR-DEMO-001'],[
            'project_id'=>$project->id,'material_source_id'=>$materials['AIR-DEMO']->id,'sample_number'=>'SPL-AIR-DEMO',
            'received_at'=>'2026-07-15','sampled_at'=>'2026-07-15','tested_at'=>'2026-07-16','technician'=>'Teknisi Demo',
            'water_source'=>'Instalasi air laboratorium','sampling_location'=>'Bak penampung contoh','color'=>'Jernih','odor'=>'Tidak berbau',
            'ph'=>7.2,'silt_content'=>12,'organic_content'=>8,'chloride'=>110,'sulfate'=>95,'dissolved_solids'=>340,
            'comparative_mortar_strength'=>96.5,'status'=>'draft','notes'=>'DATA CONTOH - belum untuk laporan resmi.',
            'created_by'=>$admin->id,'updated_by'=>$admin->id,
        ]);

        FineAggregateTest::updateOrCreate(['test_number'=>'FIN-DEMO-001'],[
            'project_id'=>$project->id,'material_source_id'=>$materials['PAS-DEMO']->id,'sample_number'=>'SPL-PAS-DEMO',
            'tested_at'=>'2026-07-17','technician'=>'Teknisi Demo','quarry'=>'Quarry Pasir Contoh','supplier'=>'Supplier Agregat Contoh',
            'bulk_specific_gravity_dry'=>2.55,'specific_gravity_ssd'=>2.61,'apparent_specific_gravity'=>2.72,
            'absorption'=>2.35,'loose_bulk_density'=>1450,'compacted_bulk_density'=>1580,'field_moisture'=>5.75,
            'silt_content'=>2.1,'fineness_modulus'=>2.74,'gradation_zone'=>'Zona contoh internal 2',
            'void_percentage'=>38.0,'aggregate_condition'=>'Lembap','status'=>'draft',
            'notes'=>'DATA CONTOH - batas zona bukan nilai resmi SNI.','created_by'=>$admin->id,'updated_by'=>$admin->id,
        ]);
        CoarseAggregateTest::updateOrCreate(['test_number'=>'COA-DEMO-001'],[
            'project_id'=>$project->id,'material_source_id'=>$materials['KER-DEMO']->id,'sample_number'=>'SPL-KER-DEMO',
            'tested_at'=>'2026-07-17','technician'=>'Teknisi Demo','aggregate_type'=>'Batu pecah','quarry'=>'Quarry Batu Contoh',
            'nominal_maximum_size'=>20,'bulk_specific_gravity_dry'=>2.62,'specific_gravity_ssd'=>2.67,
            'apparent_specific_gravity'=>2.75,'absorption'=>1.85,'loose_bulk_density'=>1420,
            'compacted_bulk_density'=>1560,'field_moisture'=>2.4,'silt_content'=>0.8,'abrasion'=>23.6,
            'flakiness'=>11.2,'elongation'=>9.5,'crushed_particles'=>94,'void_percentage'=>40.5,'status'=>'draft',
            'notes'=>'DATA CONTOH - belum untuk laporan resmi.','created_by'=>$admin->id,'updated_by'=>$admin->id,
        ]);

        $calculator = app(AggregateTestCalculator::class);
        $fineRuns = [
            'moisture'=>[['container'=>0,'wet_container'=>1000,'dry_container'=>946.5],['container'=>0,'wet_container'=>1000,'dry_container'=>938.2]],
            'silt'=>[['dry_before'=>1000,'dry_after'=>978],['dry_before'=>1000,'dry_after'=>980]],
            'specific-gravity'=>[['ssd'=>500,'pyc_water'=>660,'pyc_sample_water'=>970,'oven_dry'=>488],['ssd'=>500,'pyc_water'=>661,'pyc_sample_water'=>971,'oven_dry'=>489]],
            'bulk-density'=>[['container'=>4.2,'full_container'=>11.45,'volume'=>5000,'specific_gravity'=>2.61],['container'=>4.2,'full_container'=>11.55,'volume'=>5000,'specific_gravity'=>2.61]],
            'sieve'=>[$this->sieveObservation([0,0,188.6,544.7,194.2,9.8,0], 937.3)],
        ];
        $coarseRuns = [
            'moisture'=>[['container'=>250,'wet_container'=>5250,'dry_container'=>5130],['container'=>245,'wet_container'=>5245,'dry_container'=>5125]],
            'silt'=>[['dry_before'=>5000,'dry_after'=>4960],['dry_before'=>5000,'dry_after'=>4955]],
            'specific-gravity'=>[['oven_dry'=>2000,'ssd'=>2037,'submerged'=>1275],['oven_dry'=>2000,'ssd'=>2035,'submerged'=>1272]],
            'bulk-density'=>[['container'=>7.5,'full_container'=>21.7,'volume'=>10000,'specific_gravity'=>2.67],['container'=>7.5,'full_container'=>22.1,'volume'=>10000,'specific_gravity'=>2.67]],
            'sieve'=>[$this->sieveObservation([620,1380,1900,850,200,40,10], 5000)],
            'los-angeles'=>[['initial'=>5000,'retained'=>3820],['initial'=>5000,'retained'=>3800]],
        ];
        $this->createRuns($project,$materials['PAS-DEMO'],$admin,'fine',$fineRuns,$calculator);
        $this->createRuns($project,$materials['KER-DEMO'],$admin,'coarse',$coarseRuns,$calculator);

        MixDesign::updateOrCreate(['number'=>'MD-DEMO-001-R0'],[
            'project_id'=>$project->id,'planned_at'=>'2026-07-20','designer'=>'Perencana Demo','method'=>'SNI 7656:2012',
            'concrete_type'=>'normal','fc'=>25,'design_age'=>28,'standard_deviation'=>4.5,'fcr'=>32.38,
            'slump_min'=>75,'slump_max'=>100,'max_aggregate_size'=>20,'water_cement_ratio'=>0.48,
            'water_content'=>190,'cement_content'=>395.8333,'fine_aggregate'=>720,'coarse_aggregate'=>1050,
            'absolute_volume'=>0.998,'status'=>'draft','notes'=>'DATA CONTOH. Komposisi wajib diverifikasi terhadap referensi resmi.',
            'created_by'=>$admin->id,'updated_by'=>$admin->id,
        ]);
    }

    private function sieveObservation(array $retained, float $sample): array
    {
        $keys=['r475','r236','r118','r060','r030','r015','pan'];
        $o=['sample_mass'=>$sample,'selected_zone'=>'Zona contoh internal 2'];
        foreach($keys as $i=>$key) $o[$key]=$retained[$i];
        foreach(range(1,4) as $zone) foreach($keys as $key) {
            $o["zone_{$zone}_{$key}_lower"]=max(0, 100-(array_search($key,$keys)*16)-$zone*3);
            $o["zone_{$zone}_{$key}_upper"]=min(100, $o["zone_{$zone}_{$key}_lower"]+12);
        }
        return $o;
    }

    private function createRuns(Project $project, MaterialSource $material, User $admin, string $aggregate, array $runs, AggregateTestCalculator $calculator): void
    {
        foreach($runs as $type=>$observations) {
            AggregateTestRun::updateOrCreate(['test_number'=>strtoupper("DEMO-{$aggregate}-{$type}")],[
                'project_id'=>$project->id,'material_source_id'=>$material->id,'aggregate_type'=>$aggregate,
                'test_type'=>$type,'sample_number'=>$material->sample_number,'tested_at'=>'2026-07-18',
                'technician'=>'Teknisi Demo','observations'=>$observations,
                'results'=>$calculator->calculate($aggregate,$type,$observations),'status'=>'draft',
                'notes'=>'DATA CONTOH - bukan hasil resmi.','created_by'=>$admin->id,
            ]);
        }
    }
}
