<?php

namespace Database\Seeders;

use App\Models\AggregateTestRun;
use App\Models\CementTest;
use App\Models\CoarseAggregateTest;
use App\Models\FineAggregateTest;
use App\Models\LaboratoryProfile;
use App\Models\LaboratoryWorkflow;
use App\Models\MaterialSource;
use App\Models\Project;
use App\Models\ReportApproval;
use App\Models\TestDocumentation;
use App\Models\User;
use App\Models\WaterTest;
use App\Services\AggregateTestCalculator;
use App\Services\MixDesign\MixDesign2012Calculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CompleteJmdReportDemoSeeder extends Seeder
{
    private const PROJECT_NUMBER = 'DEMO-JMD-LENGKAP-001';

    public function run(): void
    {
        $admin = User::whereIn('role', ['admin', 'administrator'])->firstOrFail();
        $approver = User::updateOrCreate(['username' => 'demo_report_approver'], [
            'name' => 'Ir. Pemeriksa Contoh, S.T., M.T.', 'email' => 'pemeriksa-demo@local.test',
            'password' => Str::password(32), 'role' => 'administrator', 'is_active' => false,
            'position' => 'Kepala Laboratorium (Data Demo)', 'institution' => 'Laboratorium Teknik Sipil',
        ]);
        Project::withTrashed()->where('number', self::PROJECT_NUMBER)->forceDelete();
        MaterialSource::withTrashed()->whereIn('code', ['DEMO-CEMENT-001', 'DEMO-WATER-001', 'DEMO-FINE-001', 'DEMO-COARSE-001'])->forceDelete();

        $project = Project::create([
            'number' => self::PROJECT_NUMBER, 'jmd_number' => 'JMD-DEMO-001', 'report_number' => 'LHU/JMD/DEMO/001/2026',
            'sample_number' => 'SPL-DEMO-001', 'request_letter_number' => '001/REQ-DEMO/VIII/2026', 'request_letter_date' => '2026-07-20',
            'materials_received_at' => '2026-07-22', 'testing_date' => '2026-07-23', 'report_date' => '2026-08-07',
            'name' => 'Pembangunan Gedung Laboratorium Beton — Contoh Lengkap', 'activity_name' => 'Pekerjaan Struktur Beton Bertulang',
            'work_package' => 'Paket Contoh Job Mix Design', 'owner' => 'Dinas Pekerjaan Umum (Data Demo)',
            'employer' => 'PPK Kegiatan Contoh', 'company_name' => 'PT Contoh Konstruksi Nusantara',
            'director_name' => 'Direktur Data Contoh', 'company_address' => 'Jl. Contoh No. 1, Kota Baubau',
            'contractor' => 'PT Contoh Konstruksi Nusantara', 'consultant' => 'CV Konsultan Contoh',
            'location' => 'Kota Baubau, Sulawesi Tenggara', 'city' => 'Baubau', 'contract_number' => '600/DEMO-KONTRAK/2026',
            'contract_date' => '2026-07-15', 'start_date' => '2026-07-20', 'end_date' => '2026-12-20',
            'person_in_charge' => 'Penanggung Jawab Contoh', 'supervisor' => 'Pengawas Contoh',
            'tester_name' => 'Teknisi Laboratorium Demo', 'reviewer_name' => $approver->name,
            'laboratory_head_name' => $approver->name, 'concrete_grade' => 'f’c 25 MPa',
            'construction_type' => 'Struktur beton bertulang', 'environment' => 'Normal',
            'notes' => 'DATA DEMO — seluruh angka dan dokumentasi hanya untuk contoh penggunaan aplikasi.',
            'status' => 'selesai', 'jmd_status' => 'completed', 'report_include_mix_design_2012' => true,
            'report_include_mix_design_2012_combined' => false, 'report_revision' => 1,
            'document_status' => 'valid', 'verification_code' => (string) Str::uuid(),
            'locked_at' => now(), 'legalized_at' => now(), 'legalized_by' => $approver->id,
            'created_by' => $admin->id, 'updated_by' => $admin->id,
        ]);

        $sources = $this->materials($project, $admin);
        $this->materialExaminations($project, $sources, $admin);
        $this->aggregateTests($project, $sources, $admin);
        $this->workflows($project, $admin);
        $documentationDirectory = storage_path('app/public/documentation/demo-jmd-complete');
        File::ensureDirectoryExists($documentationDirectory);
        File::copy(public_path('demo-assets/pengujian-agregat-demo.png'), $documentationDirectory.'/pengujian-agregat.png');
        TestDocumentation::create([
            'project_id' => $project->id, 'module' => 'pengujian-agregat', 'title' => 'Analisis Saringan Pasir dan Kerikil',
            'documented_at' => '2026-07-24', 'description' => 'Dokumentasi ilustratif/data demo pengujian agregat di laboratorium.',
            'photo_path' => 'documentation/demo-jmd-complete/pengujian-agregat.png', 'sort_order' => 1, 'created_by' => $admin->id,
        ]);
        LaboratoryProfile::firstOrCreate([], [
            'name' => 'Laboratorium Teknik Sipil', 'institution' => 'Institusi Contoh',
            'address' => 'Kota Baubau, Sulawesi Tenggara', 'head_name' => $approver->name,
            'head_position' => 'Kepala Laboratorium', 'report_footer' => 'Laporan contoh Job Mix Design — data demo.',
        ]);

        $hash = $this->documentHash($project);
        $project->update(['document_hash' => $hash]);
        ReportApproval::create([
            'approval_id' => (string) Str::uuid(), 'verification_token' => Str::random(64),
            'project_id' => $project->id, 'user_id' => $approver->id, 'revision' => 1,
            'approval_role' => 'menyetujui', 'approval_type' => 'pengesahan laporan contoh',
            'status' => 'valid', 'document_hash' => $hash, 'ip_address' => '127.0.0.1',
            'user_agent' => 'CompleteJmdReportDemoSeeder', 'approved_at' => now(),
            'notes' => 'Persetujuan otomatis khusus data demo.',
        ]);
    }

    private function materials(Project $project, User $user): array
    {
        $rows = [
            'cement' => ['Semen PCC Demo', 'PCC', 'Pabrik Semen Contoh', null],
            'water' => ['Air Bersih Laboratorium', null, null, 'Instalasi air laboratorium'],
            'fine' => ['Pasir Sungai Demo', null, null, 'Quarry Pasir Contoh'],
            'coarse' => ['Batu Pecah 10–20 mm Demo', null, null, 'Stone Crusher Contoh'],
        ];
        foreach ($rows as $type => [$name, $brand, $producer, $quarry]) {
            $result[$type] = MaterialSource::create([
                'project_id' => $project->id, 'code' => 'DEMO-'.strtoupper($type).'-001', 'type' => $type,
                'name' => $name, 'brand' => $brand, 'producer' => $producer, 'quarry' => $quarry,
                'supplier' => 'Pemasok Material Demo', 'sampled_at' => '2026-07-22',
                'sample_number' => 'SPL-'.strtoupper($type).'-01', 'condition' => 'Baik',
                'notes' => 'Sumber material contoh.', 'created_by' => $user->id,
            ]);
        }

        return $result;
    }

    private function materialExaminations(Project $project, array $sources, User $user): void
    {
        $common = ['project_id' => $project->id, 'received_at' => '2026-07-22', 'tested_at' => '2026-07-23', 'technician' => 'Teknisi Laboratorium Demo', 'status' => 'disetujui', 'created_by' => $user->id];
        CementTest::create($common + ['material_source_id' => $sources['cement']->id, 'test_number' => 'CEM-DEMO-001', 'sample_number' => 'SPL-CEMENT-01', 'cement_type' => 'PCC', 'brand' => 'Semen PCC Demo', 'batch_number' => 'BATCH-DEMO-01', 'color' => 'Abu-abu', 'package_condition' => 'Baik', 'has_lumps' => false, 'specific_gravity' => 3.15, 'fineness' => 5.2, 'normal_consistency' => 27.5, 'initial_setting_time' => 125, 'final_setting_time' => 240, 'mortar_strength' => 32.5, 'temperature' => 27]);
        WaterTest::create($common + ['material_source_id' => $sources['water']->id, 'test_number' => 'WAT-DEMO-001', 'sample_number' => 'SPL-WATER-01', 'water_source' => 'Air bersih laboratorium', 'sampling_location' => 'Bak penampung', 'sampled_at' => '2026-07-22', 'color' => 'Jernih', 'odor' => 'Tidak berbau', 'ph' => 7.2, 'silt_content' => 0, 'organic_content' => 0, 'chloride' => 12.5, 'sulfate' => 8.4, 'dissolved_solids' => 85, 'comparative_mortar_strength' => 96]);
        FineAggregateTest::create($common + ['material_source_id' => $sources['fine']->id, 'test_number' => 'FINE-DEMO-001', 'sample_number' => 'SPL-FINE-01', 'quarry' => 'Quarry Pasir Contoh', 'supplier' => 'Pemasok Material Demo', 'bulk_specific_gravity_dry' => 2.57, 'specific_gravity_ssd' => 2.62, 'apparent_specific_gravity' => 2.70, 'absorption' => 1.95, 'loose_bulk_density' => 1485, 'compacted_bulk_density' => 1605, 'field_moisture' => 4.45, 'silt_content' => 3.50, 'fineness_modulus' => 2.72, 'gradation_zone' => 'Zona II', 'void_percentage' => 38.7, 'aggregate_condition' => 'Lembap']);
        CoarseAggregateTest::create($common + ['material_source_id' => $sources['coarse']->id, 'test_number' => 'COARSE-DEMO-001', 'sample_number' => 'SPL-COARSE-01', 'aggregate_type' => 'Batu pecah', 'quarry' => 'Stone Crusher Contoh', 'nominal_maximum_size' => 19, 'bulk_specific_gravity_dry' => 2.62, 'specific_gravity_ssd' => 2.66, 'apparent_specific_gravity' => 2.73, 'absorption' => 1.35, 'loose_bulk_density' => 1510, 'compacted_bulk_density' => 1650, 'field_moisture' => 1.75, 'silt_content' => 0.65, 'abrasion' => 21.5, 'flakiness' => 12.4, 'elongation' => 10.8, 'crushed_particles' => 96, 'void_percentage' => 37.2]);
    }

    private function aggregateTests(Project $project, array $sources, User $user): void
    {
        $fine = [
            'moisture' => [['container' => 120, 'wet_container' => 620, 'dry_container' => 598], ['container' => 118, 'wet_container' => 618, 'dry_container' => 596]],
            'silt' => [['dry_before' => 500, 'dry_after' => 482], ['dry_before' => 500, 'dry_after' => 483]],
            'specific-gravity' => [['oven_dry' => 490, 'pyc_water' => 650, 'pyc_sample_water' => 950, 'ssd' => 500], ['oven_dry' => 492, 'pyc_water' => 651, 'pyc_sample_water' => 951, 'ssd' => 502]],
            'bulk-density' => [['container' => 3.2, 'full_container' => 10.63, 'volume' => 5000, 'specific_gravity' => 2.62], ['container' => 3.2, 'full_container' => 11.23, 'volume' => 5000, 'specific_gravity' => 2.62]],
            'sieve' => [['sample_mass' => 1000, 'r095' => 0, 'r475' => 40, 'r236' => 120, 'r118' => 190, 'r060' => 240, 'r030' => 220, 'r015' => 140, 'pan' => 50], ['sample_mass' => 1000, 'r095' => 0, 'r475' => 35, 'r236' => 125, 'r118' => 195, 'r060' => 235, 'r030' => 220, 'r015' => 140, 'pan' => 50]],
        ];
        $coarse = [
            'moisture' => [['container' => 150, 'wet_container' => 1150, 'dry_container' => 1133], ['container' => 152, 'wet_container' => 1152, 'dry_container' => 1135]],
            'silt' => [['dry_before' => 1000, 'dry_after' => 993], ['dry_before' => 1000, 'dry_after' => 994]],
            'specific-gravity' => [['oven_dry' => 1970, 'ssd' => 2000, 'submerged' => 1250], ['oven_dry' => 1972, 'ssd' => 2002, 'submerged' => 1251]],
            'bulk-density' => [['container' => 5.1, 'full_container' => 12.65, 'volume' => 5000, 'specific_gravity' => 2.66], ['container' => 5.1, 'full_container' => 13.35, 'volume' => 5000, 'specific_gravity' => 2.66]],
            'sieve' => [['sample_mass' => 5000, 'r750' => 0, 'r375' => 0, 'r190' => 150, 'r095' => 3300, 'r475' => 1450, 'pan' => 100], ['sample_mass' => 5000, 'r750' => 0, 'r375' => 0, 'r190' => 140, 'r095' => 3350, 'r475' => 1410, 'pan' => 100]],
            'los-angeles' => [['initial' => 5000, 'retained' => 3910], ['initial' => 5000, 'retained' => 3940]],
        ];
        foreach (['fine' => $fine, 'coarse' => $coarse] as $aggregate => $tests) {
            foreach ($tests as $type => $observations) {
                $calculator = app(AggregateTestCalculator::class);
                $result = $calculator->calculate($aggregate, $type, $observations);
                $result['formula'] = match ($type) {
                    'moisture' => 'Kadar air = ((massa basah - wadah) - (massa kering - wadah)) / (massa kering - wadah) x 100%.',
                    'silt' => 'Kadar lumpur = (massa sebelum - massa sesudah) / massa sebelum x 100%.',
                    'specific-gravity' => $aggregate === 'fine'
                        ? 'BJ kering = E / (D + B - C); BJ SSD = B / (D + B - C); penyerapan = (B - E) / E x 100%.'
                        : 'BJ kering = C / (A - B); BJ SSD = A / (A - B); penyerapan = (A - C) / C x 100%.',
                    'bulk-density' => 'Massa agregat = massa penuh - massa bejana; berat isi = massa agregat / volume; rongga = (BJ x 1.000 - berat isi) / (BJ x 1.000) x 100%.',
                    'sieve' => 'Persen tertahan kumulatif = massa kumulatif / massa sampel x 100%; FM = jumlah persen kumulatif saringan standar / 100.',
                    'los-angeles' => 'Keausan = (massa awal - massa tertahan setelah pengujian) / massa awal x 100%.',
                };
                AggregateTestRun::create([
                    'project_id' => $project->id, 'material_source_id' => $sources[$aggregate]->id,
                    'test_number' => 'DEMO-'.strtoupper($aggregate).'-'.strtoupper(str_replace('-', '', $type)),
                    'aggregate_type' => $aggregate, 'test_type' => $type,
                    'sample_number' => 'SPL-'.strtoupper($aggregate).'-01', 'tested_at' => '2026-07-24',
                    'technician' => 'Teknisi Laboratorium Demo', 'observations' => $observations,
                    'results' => $result,
                    'status' => 'disetujui', 'notes' => 'Dua observasi lengkap — data demo.', 'created_by' => $user->id,
                ]);
            }
        }
    }

    private function workflows(Project $project, User $user): void
    {
        $input = ['cement_sg' => 3.15, 'coarse_sg' => 2.66, 'fine_sg' => 2.62, 'fc' => 25, 'sd' => 4, 'sd_additional' => 0, 'deviation_factor' => 1.64, 'water' => 185, 'wc_ratio' => 0.5, 'coarse_density' => 1650, 'fine_fm' => 2.72, 'fresh_density' => 2400, 'max_size' => 19, 'air_content' => 2, 'fine_moisture' => 4.45, 'fine_absorption' => 1.95, 'coarse_moisture' => 1.75, 'coarse_absorption' => 1.35, 'trial_volume_liter' => 30, 'waste' => 5, 'slump_min' => 75, 'slump_max' => 100, 'slump_design' => 85];
        $mix = app(MixDesign2012Calculator::class)->calculate($input);
        $this->workflow($project, $user, 'mix-design-2012', 'MD12-DEMO-001', $input, $mix, '2026-07-25');
        $this->workflow($project, $user, 'combined-aggregate', 'COMB-DEMO-001', ['fine_percent' => 42, 'coarse_percent' => 58, 'total_mass' => 1000], ['Total agregat (%)' => 100, 'Agregat halus (kg)' => 420, 'Agregat kasar (kg)' => 580, 'Status' => 'Memenuhi 100%'], '2026-07-25');
        $this->workflow($project, $user, 'moisture-correction', 'MOI-DEMO-001', ['fine_ssd' => $mix['fine_ssd'], 'fine_absorption' => 1.95, 'fine_moisture' => 4.45, 'coarse_ssd' => $mix['coarse_ssd'], 'coarse_absorption' => 1.35, 'coarse_moisture' => 1.75, 'design_water' => 185], ['Pasir lapangan (kg)' => $mix['fine_field'], 'Kerikil lapangan (kg)' => $mix['coarse_field'], 'Air bebas agregat (kg)' => $mix['fine_free_water'] + $mix['coarse_free_water'], 'Air ditambahkan (kg)' => $mix['water_added']], '2026-07-25');
        $this->workflow($project, $user, 'trial-mix', 'TRI-DEMO-001', ['trial_volume' => 30, 'waste' => 5, 'cement_m3' => $mix['cement'], 'water_m3' => $mix['water_added'], 'fine_m3' => $mix['fine_field'], 'coarse_m3' => $mix['coarse_field']], ['Semen (kg)' => $mix['trial_cement'], 'Air (kg)' => $mix['trial_water'], 'Pasir (kg)' => $mix['trial_fine'], 'Kerikil (kg)' => $mix['trial_coarse'], 'Faktor volume' => .0315], '2026-07-26');
        $this->workflow($project, $user, 'fresh-concrete', 'FRE-DEMO-001', ['target_slump' => 85, 'actual_slump' => 88, 'theoretical_density' => $mix['total_fresh_mass'], 'actual_density' => 2392, 'batch_mass' => 75.35, 'design_volume' => .0315], ['Selisih slump (mm)' => 3, 'Volume aktual (m³)' => .0315, 'Hasil volume' => 1, 'Selisih berat isi (kg/m³)' => 2392 - $mix['total_fresh_mass']], '2026-07-26');
        $this->workflow($project, $user, 'specimen', 'SPE-DEMO-001', ['diameter' => 150, 'height' => 300, 'weight' => 12.45, 'test_age' => 28], ['Luas tekan (mm²)' => pi() * 150 ** 2 / 4, 'Volume benda uji (m³)' => pi() * .15 ** 2 / 4 * .3, 'Berat isi benda uji (kg/m³)' => 2348, 'Umur rencana (hari)' => 28], '2026-07-26');
        $details = [];
        foreach ([505, 512, 498] as $index => $load) {
            $actual = $load * 1000 / (pi() * 150 ** 2 / 4);
            $details[] = ['number' => $index + 1, 'cast_date' => '2026-07-26', 'test_date' => '2026-08-23', 'diameter' => 150, 'height' => 300, 'weight' => 12.4 + $index * .05, 'load_kn' => $load, 'age_days' => 28, 'area_mm2' => pi() * 150 ** 2 / 4, 'actual_mpa' => $actual, 'age_factor' => 1, 'estimated_28_mpa' => $actual, 'estimated_k_kgcm2' => $actual * 10.19716213];
        }
        $values = array_column($details, 'estimated_28_mpa');
        $mean = array_sum($values) / 3;
        $sd = sqrt(array_sum(array_map(fn ($x) => ($x - $mean) ** 2, $values)) / 2);
        $characteristic = $mean - 1.64 * $sd;
        $strength = ['Jumlah benda uji' => 3, 'Sasaran f\'c (MPa)' => 25, 'Rata-rata perkiraan 28 hari (MPa)' => $mean, 'Standar deviasi sampel (MPa)' => $sd, 'Kuat tekan karakteristik (MPa)' => $characteristic, 'Mutu karakteristik (kg/cm²)' => $characteristic * 10.19716213, 'Status' => $characteristic >= 25 ? 'Memenuhi' : 'Tidak memenuhi', 'detail_rows' => $details];
        $this->workflow($project, $user, 'compressive-strength', 'COM-DEMO-001', ['target_fc' => 25, 'mix_design_number' => 'MD12-DEMO-001', 'rows' => $details], $strength, '2026-08-23');
        $this->workflow($project, $user, 'evaluation', 'EVA-DEMO-001', ['target_fc' => 25, 'actual_fc' => $characteristic, 'slump_min' => 75, 'slump_max' => 100, 'actual_slump' => 88], ['Pencapaian kuat tekan (%)' => $characteristic / 25 * 100, 'Status slump' => 'Sesuai', 'Kesimpulan' => $characteristic >= 25 ? 'Campuran diterima' : 'Perlu penyesuaian'], '2026-08-23');
    }

    private function workflow(Project $project, User $user, string $type, string $number, array $input, array $result, string $date): void
    {
        LaboratoryWorkflow::create(['project_id' => $project->id, 'type' => $type, 'number' => $number, 'work_date' => $date, 'input_data' => $input, 'result_data' => $result, 'status' => 'disetujui', 'notes' => 'Data demo lengkap.', 'created_by' => $user->id]);
    }

    private function documentHash(Project $project): string
    {
        $records = collect();
        foreach ([['Pemeriksaan Semen', CementTest::class], ['Pemeriksaan Air', WaterTest::class], ['Pemeriksaan Pasir', FineAggregateTest::class], ['Pemeriksaan Kerikil', CoarseAggregateTest::class]] as [$module, $model]) {
            foreach ($model::where('project_id', $project->id)->latest()->get() as $record) {
                $records->push([$module, $record->test_number, $record->tested_at?->format('c'), 'Data karakteristik material']);
            }
        }
        foreach (AggregateTestRun::where('project_id', $project->id)->latest()->get() as $record) {
            $result = collect($record->results['averages'] ?? [])->map(fn ($value, $key) => ucwords(str_replace('_', ' ', $key)).': '.number_format($value, 3, ',', '.'))->join('; ');
            $records->push([ucwords(str_replace('-', ' ', $record->test_type)).' '.($record->aggregate_type === 'fine' ? 'Pasir' : 'Kerikil'), $record->test_number, $record->tested_at?->format('c'), $result]);
        }
        foreach (LaboratoryWorkflow::where('project_id', $project->id)->latest()->get() as $record) {
            $module = match ($record->type) {
                'mix-design-2012' => 'Desain Campuran SNI 7656:2012', 'compressive-strength' => 'Kuat Tekan', default => ucwords(str_replace('-', ' ', $record->type))
            };
            $result = collect($record->result_data)->reject(fn ($value) => is_array($value))->take(3)->map(fn ($value, $key) => $key.': '.(is_numeric($value) ? number_format($value, 3, ',', '.') : $value))->join('; ');
            $records->push([$module, $record->number, $record->work_date?->format('c'), $result]);
        }
        $records = $records->sortByDesc(fn ($record) => $record[2])->values()->all();

        return hash('sha256', json_encode(['project' => $project->only(['number', 'name', 'owner', 'location', 'concrete_grade', 'construction_type', 'report_include_mix_design_2012', 'report_include_mix_design_2012_combined']), 'revision' => $project->report_revision, 'records' => $records], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }
}
