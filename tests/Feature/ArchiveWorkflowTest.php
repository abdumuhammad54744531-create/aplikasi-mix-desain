<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_can_be_archived_restored_and_deleted_permanently(): void
    {
        $user = User::factory()->create([
            'username' => 'archive-admin',
            'role' => 'admin',
            'access_level' => 'edit',
        ]);
        $project = Project::create([
            'number' => 'ARSIP-001',
            'name' => 'Pengujian Arsip',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete(route('archive.store', ['projects', $project->id]))
            ->assertRedirect();
        $this->assertSoftDeleted('projects', ['id' => $project->id]);

        $this->actingAs($user)
            ->get(route('archive.index'))
            ->assertOk()
            ->assertSee('ARSIP-001')
            ->assertSee('Pulihkan')
            ->assertSee('Hapus Permanen');

        $this->actingAs($user)
            ->patch(route('archive.restore', ['projects', $project->id]))
            ->assertRedirect();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'deleted_at' => null]);

        $this->actingAs($user)
            ->delete(route('archive.store', ['projects', $project->id]))
            ->assertRedirect();
        $this->actingAs($user)
            ->delete(route('archive.destroy', ['projects', $project->id]))
            ->assertRedirect();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
