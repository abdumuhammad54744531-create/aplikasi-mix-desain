<?php

namespace Tests\Feature;

use App\Enums\JmdStatus;
use App\Models\Jmd\DesignCriterion;
use App\Models\Jmd\MoistureTest;
use App\Models\Jmd\ProjectMaterial;
use App\Models\MaterialSource;
use App\Models\Project;
use App\Models\User;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JmdDomainLayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_models_cast_status_and_expose_normalized_relations(): void
    {
        $user = User::factory()->create(['username' => 'domain-jmd']);
        $project = Project::create(['number' => 'PRJ-DOMAIN-1', 'name' => 'Domain JMD', 'created_by' => $user->id]);
        $source = MaterialSource::create(['code' => 'MAT-DOMAIN-1', 'type' => 'Pasir', 'name' => 'Pasir Uji']);
        $material = ProjectMaterial::create(['project_id' => $project->id, 'material_source_id' => $source->id, 'material_type' => 'fine_aggregate']);
        DesignCriterion::create(['project_id' => $project->id, 'revision_number' => 0]);
        $test = MoistureTest::create([
            'project_id' => $project->id, 'jmd_project_material_id' => $material->id,
            'test_number' => 'MC-DOMAIN-1', 'aggregate_type' => 'fine',
        ]);
        $test->items()->createMany([
            ['observation_number' => 1, 'container_mass' => 20, 'wet_container_mass' => 120, 'dry_container_mass' => 115],
            ['observation_number' => 2, 'container_mass' => 21, 'wet_container_mass' => 121, 'dry_container_mass' => 116],
        ]);

        $this->assertSame(JmdStatus::Draft, $project->fresh()->jmd_status);
        $this->assertCount(1, $project->jmdMaterials);
        $this->assertCount(1, $project->designCriteria);
        $this->assertCount(2, $project->moistureTests()->first()->items);
        $this->assertTrue($test->project->is($project));
        $this->assertTrue($test->projectMaterial->is($material));
    }

    public function test_project_policy_enforces_lock_and_approval_authority(): void
    {
        $policy = new ProjectPolicy;
        $project = new Project;
        $editor = User::factory()->make(['role' => 'teknisi', 'access_level' => 'edit', 'approval_authority' => 'pemeriksa,mengetahui']);
        $reader = User::factory()->make(['role' => 'teknisi', 'access_level' => 'read']);

        $this->assertTrue($policy->updateJmd($editor, $project));
        $this->assertFalse($policy->updateJmd($reader, $project));
        $this->assertTrue($policy->approve($editor, $project, 'pemeriksa'));
        $this->assertFalse($policy->approve($editor, $project, 'menyetujui'));

        $project->locked_at = now();
        $this->assertFalse($policy->updateJmd($editor, $project));
        $this->assertTrue($policy->createRevision($editor, $project));
    }
}
