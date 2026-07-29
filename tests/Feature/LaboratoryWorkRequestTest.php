<?php

namespace Tests\Feature;

use App\Models\LaboratoryWorkRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaboratoryWorkRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_brochure_can_be_opened_without_login(): void
    {
        $this->get(route('lab-services.brochure'))
            ->assertOk()
            ->assertSee('Permohonan Pengujian Laboratorium Beton')
            ->assertSee('Buat Permohonan')
            ->assertSee('Pemeriksaan karakteristik material');
    }

    public function test_applicant_can_register_submit_and_only_access_applicant_area(): void
    {
        Storage::fake('public');
        $this->post(route('applicant.register.store'), [
            'name' => 'Pemohon Satu',
            'institution' => 'CV Penguji Beton',
            'username' => 'pemohon1',
            'email' => 'pemohon1@example.test',
            'password' => 'Pemohon@123',
            'password_confirmation' => 'Pemohon@123',
        ])->assertRedirect(route('lab-requests.index'));

        $applicant = User::where('username', 'pemohon1')->firstOrFail();
        $this->assertSame('pemohon', $applicant->role);
        $this->actingAs($applicant)->get(route('lab-requests.index'))
            ->assertOk()
            ->assertSee('HALAMAN KHUSUS PEMOHON')
            ->assertSee('Unggah surat permohonan resmi')
            ->assertDontSee('Riwayat Audit');

        $this->actingAs($applicant)->post(route('lab-requests.store'), [
            'phone' => '08123456789',
            'institution' => 'CV Penguji Beton',
            'work_name' => 'Pembangunan Gedung A',
            'project_number' => 'PRJ-001',
            'work_package' => 'Pekerjaan Struktur Gedung A',
            'owner' => 'PT Pemilik Gedung',
            'contractor' => 'CV Penguji Beton',
            'consultant' => 'CV Konsultan Pengawas',
            'service_type' => 'desain-campuran',
            'sample_description' => 'Pasir dan kerikil',
            'sample_quantity' => 2,
            'requested_date' => now()->addDay()->format('Y-m-d'),
            'project_location' => 'Kota Baubau',
            'contract_number' => 'KONTRAK-001',
            'contract_date' => now()->format('Y-m-d'),
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'concrete_grade' => "f'c 25 MPa",
            'construction_type' => 'Gedung',
            'environment' => 'Lingkungan normal',
            'application_letter' => UploadedFile::fake()->create('surat-permohonan.pdf', 200, 'application/pdf'),
        ])->assertRedirect(route('lab-requests.index'));

        $this->assertDatabaseHas('laboratory_work_requests', [
            'user_id' => $applicant->id,
            'work_name' => 'Pembangunan Gedung A',
            'status' => 'diajukan',
        ]);
        $storedRequest = LaboratoryWorkRequest::where('user_id', $applicant->id)->firstOrFail();
        Storage::disk('public')->assertExists($storedRequest->application_letter_path);

        $this->actingAs($applicant)->get(route('projects.index'))
            ->assertRedirect(route('lab-requests.index'));
    }

    public function test_staff_can_review_and_update_applicant_request(): void
    {
        $applicant = User::factory()->create([
            'username' => 'pemohon2',
            'role' => 'pemohon',
            'access_level' => 'read',
        ]);
        $staff = User::factory()->create([
            'username' => 'admin-permohonan',
            'role' => 'administrator',
            'access_level' => 'edit',
        ]);
        $request = LaboratoryWorkRequest::create([
            'user_id' => $applicant->id,
            'request_number' => 'PLAB-TEST-001',
            'applicant_name' => $applicant->name,
            'phone' => '08123456789',
            'work_name' => 'Pengujian Beton',
            'project_number' => 'PRJ-UJI-002',
            'work_package' => 'Paket Pengujian Struktur',
            'owner' => 'PT Pemohon',
            'contractor' => 'CV Pelaksana',
            'consultant' => 'CV Konsultan',
            'service_type' => 'kuat-tekan',
            'sample_description' => 'Silinder beton',
            'sample_quantity' => 3,
            'project_location' => 'Kota Baubau',
            'person_in_charge' => 'Penanggung Jawab',
            'supervisor' => 'Pengawas',
            'concrete_grade' => "f'c 30 MPa",
            'construction_type' => 'Gedung bertingkat',
            'application_letter_path' => 'application-letters/2026/surat-uji.pdf',
            'status' => 'diajukan',
        ]);

        $this->actingAs($staff)->get(route('lab-requests.index'))
            ->assertOk()
            ->assertSee('PLAB-TEST-001')
            ->assertSee('Pengujian Beton')
            ->assertSee('Buka Surat Permohonan')
            ->assertDontSee('Formulir Permohonan Baru');

        $this->actingAs($staff)->patch(route('lab-requests.status', $request), [
            'status' => 'dijadwalkan',
            'admin_notes' => 'Pengujian dijadwalkan hari Senin.',
        ])->assertRedirect();

        $this->assertDatabaseHas('laboratory_work_requests', [
            'id' => $request->id,
            'status' => 'dijadwalkan',
            'admin_notes' => 'Pengujian dijadwalkan hari Senin.',
        ]);

        $this->actingAs($staff)->post(route('lab-requests.approve-project', $request))
            ->assertRedirect();

        $request->refresh();
        $this->assertNotNull($request->project_id);
        $this->assertDatabaseHas('projects', [
            'id' => $request->project_id,
            'number' => 'PRJ-UJI-002',
            'name' => 'Pengujian Beton',
            'owner' => 'PT Pemohon',
            'concrete_grade' => "f'c 30 MPa",
        ]);

        $this->actingAs($staff)->post(route('lab-requests.approve-project', $request))
            ->assertRedirect();
        $this->assertDatabaseCount('projects', 1);
    }
}
