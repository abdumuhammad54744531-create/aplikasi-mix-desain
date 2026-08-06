<?php

namespace Tests\Feature;

use App\Models\LaboratoryWorkflow;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaboratoryWorkflowPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_concrete_workflow_is_loaded_and_updated_by_its_record_id(): void
    {
        $user = User::factory()->create(['username' => 'workflow-tester']);
        $project = Project::create([
            'number' => 'WF-001', 'name' => 'Proyek Alur Beton', 'status' => 'aktif', 'created_by' => $user->id,
        ]);
        $payload = [
            'project_id' => $project->id,
            'work_date' => '2026-08-01',
            'data' => ['fine_percent' => 40, 'coarse_percent' => 60, 'total_mass' => 0],
            'notes' => 'Komposisi pertama',
        ];

        $this->actingAs($user)->post(route('workflow.store', 'combined-aggregate'), $payload)->assertRedirect();
        $record = LaboratoryWorkflow::sole();

        $this->get(route('workflow.index', ['type' => 'combined-aggregate', 'project' => $project->id]))
            ->assertOk()
            ->assertViewHas('savedRecords', fn ($saved) => $saved->get($project->id)['id'] === $record->id
                && (float) $saved->get($project->id)['input_data']['fine_percent'] === 40.0);

        $payload['workflow_id'] = $record->id;
        $payload['data']['fine_percent'] = 45;
        $payload['data']['coarse_percent'] = 55;
        $payload['notes'] = 'Hanya komposisi diperbaiki';
        $this->post(route('workflow.store', 'combined-aggregate'), $payload)->assertRedirect();

        $this->assertDatabaseCount('laboratory_workflows', 1);
        $record->refresh();
        $this->assertEquals(45.0, $record->input_data['fine_percent']);
        $this->assertEquals(55.0, $record->input_data['coarse_percent']);
        $this->assertEquals(0.0, $record->input_data['total_mass']);
        $this->assertSame('Hanya komposisi diperbaiki', $record->notes);

        $otherProject = Project::create([
            'number' => 'WF-002', 'name' => 'Proyek Alur Lain', 'status' => 'aktif', 'created_by' => $user->id,
        ]);
        $payload['project_id'] = $otherProject->id;
        $this->post(route('workflow.store', 'combined-aggregate'), $payload)->assertNotFound();
        $this->assertDatabaseCount('laboratory_workflows', 1);
    }
}
