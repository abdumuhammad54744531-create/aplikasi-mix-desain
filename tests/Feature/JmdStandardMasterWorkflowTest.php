<?php

namespace Tests\Feature;

use App\Models\Jmd\SiltTest;
use App\Models\Project;
use App\Models\StandardReference;
use App\Models\StandardTableHeader;
use App\Models\StandardTableValue;
use App\Models\User;
use App\Services\Jmd\StandardMasterService;
use Database\Seeders\JmdStandardMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JmdStandardMasterWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_baseline_seeder_creates_twenty_catalog_tables_idempotently(): void
    {
        $this->seed(JmdStandardMasterSeeder::class);
        $this->seed(JmdStandardMasterSeeder::class);

        $this->assertDatabaseCount('standard_references', 1);
        $this->assertDatabaseCount('standard_table_headers', 20);
        $this->assertSame(array_keys(StandardMasterService::CATALOG), StandardTableHeader::orderBy('id')->pluck('key')->all());
        $this->assertDatabaseHas('standard_table_values', ['row_key' => 'fine', 'numeric_value' => 5]);
        $this->assertDatabaseHas('standard_table_values', ['row_key' => 'coarse', 'numeric_value' => 40]);
    }

    public function test_only_administrator_can_manage_master_standard(): void
    {
        $admin = $this->user('administrator', 'edit', 'master-admin');
        $technician = $this->user('teknisi', 'edit', 'master-tech');

        $this->actingAs($technician)->get(route('jmd.standards.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('jmd.standards.index'))->assertOk();
        $this->post(route('jmd.standards.store'), [
            'name' => 'Referensi Laboratorium', 'standard_number' => 'LAB-001', 'standard_year' => '2026',
        ])->assertRedirect();
        $this->assertDatabaseHas('standard_references', ['standard_number' => 'LAB-001', 'revision_number' => 1]);
    }

    public function test_reference_and_value_revisions_preserve_old_versions(): void
    {
        $this->seed(JmdStandardMasterSeeder::class);
        $admin = $this->user('administrator', 'edit', 'revision-admin');
        $reference = StandardReference::with('tables.values')->sole();
        $oldTable = $reference->tables->firstWhere('key', 'silt_limits');
        $oldValue = $oldTable->values->firstWhere('row_key', 'fine');

        $this->actingAs($admin)->post(route('jmd.standards.revise', $reference), [
            'name' => $reference->name, 'standard_number' => $reference->standard_number,
            'standard_year' => $reference->standard_year, 'effective_at' => '2026-08-08',
            'description' => 'Revisi konfigurasi terverifikasi.',
        ])->assertRedirect();

        $revision = StandardReference::where('revision_number', 2)->sole();
        $this->assertFalse($reference->fresh()->is_active);
        $this->assertTrue($revision->is_active);
        $this->assertSame($reference->id, $revision->supersedes_id);
        $this->assertCount(20, $revision->tables);

        $copiedTable = $revision->tables()->where('key', 'silt_limits')->sole();
        $copiedValue = $copiedTable->values()->where('row_key', 'fine')->sole();
        $this->put(route('jmd.standard-values.update', $copiedValue), [
            'row_key' => 'fine', 'numeric_value' => 4.5, 'unit' => '%', 'is_active' => 1,
        ])->assertRedirect();

        $latestTable = $revision->tables()->where('key', 'silt_limits')->where('is_active', true)->sole();
        $this->assertSame(3, $latestTable->revision_number);
        $this->assertEquals(4.5, (float) $latestTable->values()->where('row_key', 'fine')->sole()->numeric_value);
        $this->assertEquals(5.0, (float) $oldValue->fresh()->numeric_value);
        $this->assertEquals(5.0, (float) $copiedValue->fresh()->numeric_value);
    }

    public function test_material_test_uses_server_resolved_table_value_and_snapshot(): void
    {
        $this->seed(JmdStandardMasterSeeder::class);
        $user = $this->user('teknisi', 'edit', 'standard-tester');
        $project = Project::create(['number' => 'STD-1', 'name' => 'Proyek Standar', 'status' => 'aktif', 'created_by' => $user->id]);
        $fineLimit = StandardTableValue::where('row_key', 'fine')->whereHas('header', fn ($query) => $query->where('key', 'silt_limits'))->sole();
        $payload = [
            'value_source' => 'table', 'standard_table_value_id' => $fineLimit->id,
            'standard_source' => 'SUMBER PALSU', 'aggregate_type' => 'fine', 'limit_percent' => 99,
            'tested_at' => '2026-08-07', 'technician' => 'Teknisi',
            'observations' => [
                ['container_mass' => 20, 'before_wash_container_mass' => 120, 'after_wash_container_mass' => 117],
                ['container_mass' => 20, 'before_wash_container_mass' => 120, 'after_wash_container_mass' => 116],
            ],
        ];

        $this->actingAs($user)->post(route('jmd.material-tests.silt.store', $project), $payload)
            ->assertSessionHasNoErrors()->assertRedirect();

        $test = SiltTest::sole();
        $this->assertEquals(5.0, (float) $test->limit_percent);
        $this->assertSame('table', data_get($test->standard_snapshot, 'mode'));
        $this->assertSame($fineLimit->id, data_get($test->standard_snapshot, 'value_id'));
        $this->assertStringNotContainsString('SUMBER PALSU', data_get($test->standard_snapshot, 'source'));
    }

    public function test_manual_mode_requires_reason_and_aggregate_mismatch_is_rejected(): void
    {
        $this->seed(JmdStandardMasterSeeder::class);
        $user = $this->user('teknisi', 'edit', 'standard-validation');
        $project = Project::create(['number' => 'STD-2', 'name' => 'Proyek Validasi', 'status' => 'aktif', 'created_by' => $user->id]);
        $coarseLimit = StandardTableValue::where('row_key', 'coarse')->where('numeric_value', 1)
            ->whereHas('header', fn ($query) => $query->where('key', 'silt_limits'))->sole();
        $observations = [
            ['container_mass' => 20, 'before_wash_container_mass' => 120, 'after_wash_container_mass' => 117],
            ['container_mass' => 20, 'before_wash_container_mass' => 120, 'after_wash_container_mass' => 116],
        ];

        $this->actingAs($user)->post(route('jmd.material-tests.silt.store', $project), [
            'value_source' => 'manual', 'standard_source' => 'Acuan manual', 'aggregate_type' => 'fine',
            'limit_percent' => 5, 'tested_at' => '2026-08-07', 'technician' => 'Teknisi', 'observations' => $observations,
        ])->assertSessionHasErrors('manual_standard_reason');

        $this->post(route('jmd.material-tests.silt.store', $project), [
            'value_source' => 'table', 'standard_table_value_id' => $coarseLimit->id,
            'aggregate_type' => 'fine', 'limit_percent' => 5, 'tested_at' => '2026-08-07',
            'technician' => 'Teknisi', 'observations' => $observations,
        ])->assertSessionHasErrors('standard_table_value_id');
        $this->assertDatabaseCount('silt_tests', 0);
    }

    private function user(string $role, string $access, string $username): User
    {
        return User::factory()->create(['username' => $username, 'role' => $role, 'access_level' => $access]);
    }
}
