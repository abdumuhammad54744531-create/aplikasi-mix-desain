<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ReportSetting;
use App\Models\User;
use App\Models\LaboratoryWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpandedReportConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_project_identity_location_and_map_are_saved_and_rendered_in_report(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['username' => 'project-admin', 'role' => 'administrator']);
        $map = UploadedFile::fake()->createWithContent(
            'site-map.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
        );

        $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Pembangunan Gedung Laboratorium',
            'work_package' => 'Paket Struktur Beton',
            'owner' => 'Universitas Contoh',
            'contractor' => 'PT Pelaksana',
            'consultant' => 'CV Konsultan',
            'contract_number' => '017/LK/VII/2026',
            'contract_date' => '2026-07-17',
            'start_date' => '2026-07-20',
            'end_date' => '2026-12-20',
            'concrete_grade' => '25 MPa',
            'construction_type' => 'Bangunan Gedung',
            'location_description' => 'Lokasi berada pada kawasan kampus utama.',
            'location_address' => 'Jalan Pendidikan, Kota Baubau',
            'latitude' => -5.4667,
            'longitude' => 122.6333,
            'coordinate_format' => 'dms',
            'map_caption' => 'Gambar 1. Peta lokasi pengujian',
            'map_image' => $map,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $project = Project::sole();
        $this->assertSame('Paket Struktur Beton', $project->work_package);
        $this->assertSame('Jalan Pendidikan, Kota Baubau', $project->location_address);
        Storage::disk('public')->assertExists($project->map_image);
        LaboratoryWorkflow::create([
            'project_id' => $project->id, 'type' => 'mix-design-2012',
            'number' => 'MD-PROJECT-001', 'work_date' => '2026-08-08',
            'input_data' => [], 'result_data' => [], 'status' => 'disetujui',
        ]);

        $this->get(route('workflow.report.final', $project))
            ->assertOk()
            ->assertSee('Paket Struktur Beton')
            ->assertSee('017/LK/VII/2026')
            ->assertSee('Lokasi berada pada kawasan kampus utama.')
            ->assertSee('Gambar 1. Peta lokasi pengujian')
            ->assertSee('cover-project-name');
    }

    public function test_report_header_typography_address_and_signer_settings_are_persisted_and_previewed(): void
    {
        $user = User::factory()->create(['username' => 'setting-admin', 'role' => 'administrator']);
        $lines = [];
        foreach ([['LAB UJI BETON', 21], ['PROGRAM STUDI TEKNIK SIPIL', 16], ['UNIVERSITAS CONTOH', 13], ['TERAKREDITASI', 10], ['LAYANAN PENGUJIAN BETON', 9]] as [$text, $size]) {
            $lines[] = ['text' => $text, 'size' => $size, 'font' => 'Times New Roman', 'align' => 'center', 'margin_top' => 0, 'margin_bottom' => 1, 'line_height' => 1.1, 'bold' => 1, 'uppercase' => 1];
        }

        $this->actingAs($user)->patch(route('report-settings.update'), [
            'margin_top' => 16, 'margin_right' => 14, 'margin_bottom' => 18, 'margin_left' => 14,
            'font_family' => 'Times New Roman', 'font_size' => 11,
            'report_heading_size' => 15, 'report_subheading_size' => 12,
            'report_table_size' => 10, 'report_caption_size' => 9,
            'report_line_height' => 1.2,
            'signer_name' => 'Dr. Pemeriksa Utama', 'signer_position' => 'Kepala Laboratorium',
            'signer_identity' => 'NIDN 00112233',
            'examiner_address' => 'Jalan Laboratorium Nomor 1', 'examiner_city' => 'Baubau',
            'examiner_province' => 'Sulawesi Tenggara', 'examiner_postal_code' => '93721',
            'examiner_phone' => '0402-123456', 'examiner_email' => 'lab@example.test',
            'examiner_website' => 'lab.example.test',
            'logo_left_position' => 'left', 'logo_right_position' => 'right',
            'logo_left_width' => 24, 'logo_right_width' => 22,
            'logo_left_height' => 20, 'logo_right_height' => 18,
            'logo_left_x' => 1, 'logo_left_y' => 2, 'logo_right_x' => -1, 'logo_right_y' => 2,
            'header_lines_enabled' => 1, 'header_line_1_width' => 1.5,
            'header_line_2_width' => 0.6, 'header_line_gap' => 1.2,
            'header_to_line_gap' => 3, 'line_to_content_gap' => 5,
            'header_lines' => $lines,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $setting = ReportSetting::firstOrFail();
        $this->assertSame('Dr. Pemeriksa Utama', $setting->signer_name);
        $this->assertSame('LAB UJI BETON', $setting->header_lines[0]['text']);
        $this->get(route('report-settings.edit'))
            ->assertOk()
            ->assertSee('Jenis huruf seluruh isi laporan (di luar kop)')
            ->assertSee('Garamond')
            ->assertSee('Pengaturan Kop Laporan')
            ->assertSee('Baris 5')
            ->assertSee('Preview Langsung');
        $this->get(route('report-settings.preview'))
            ->assertOk()
            ->assertSee('font-family:"Times New Roman"', false)
            ->assertSee('LAB UJI BETON')
            ->assertSee('Jalan Laboratorium Nomor 1')
            ->assertSee('Dr. Pemeriksa Utama');
    }

    public function test_configured_permission_matrix_restricts_routes_and_sidebar(): void
    {
        $user = User::factory()->create([
            'username' => 'project-reader',
            'role' => 'teknisi',
            'access_level' => 'edit',
            'permissions_configured' => true,
            'permissions' => ['projects.view'],
        ]);

        $this->actingAs($user)->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Data Proyek')
            ->assertDontSee('Pengaturan Laporan');
        $this->post(route('projects.store'), ['name' => 'Tidak boleh dibuat'])->assertForbidden();
        $this->get(route('report-settings.edit'))->assertForbidden();
        $this->assertDatabaseMissing('projects', ['name' => 'Tidak boleh dibuat']);
    }
}
