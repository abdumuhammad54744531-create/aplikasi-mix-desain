<?php

namespace Tests\Feature;

use App\Models\MaterialSource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimplifiedDataEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_uses_only_requested_fields_without_erasing_legacy_data(): void
    {
        $user=User::factory()->create(['username'=>'simple-project','access_level'=>'edit']);
        $this->actingAs($user)->post(route('projects.store'),[
            'name'=>'Proyek Ringkas','owner'=>'Pemohon A','location'=>'Buton','contract_number'=>'K-01','construction_type'=>'Gedung',
        ])->assertRedirect();
        $project=Project::sole();
        $project->update(['contractor'=>'Kontraktor Lama']);

        $this->put(route('projects.update',$project),[
            'name'=>'Proyek Ringkas Diperbarui','owner'=>'Pemohon A','location'=>'Baubau','contract_number'=>'K-01','construction_type'=>'Gedung',
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('Proyek Ringkas Diperbarui',$project->name);
        $this->assertSame('Kontraktor Lama',$project->contractor);
        $this->get(route('projects.index'))->assertOk()->assertSee('Nama proyek')->assertSee('Paket pekerjaan');
    }

    public function test_material_filter_separates_projects_and_simplified_form_fields(): void
    {
        $user=User::factory()->create(['username'=>'simple-material','access_level'=>'edit']);
        $first=Project::create(['number'=>'MAT-F-01','name'=>'Proyek Pertama','status'=>'aktif','created_by'=>$user->id]);
        $second=Project::create(['number'=>'MAT-F-02','name'=>'Proyek Kedua','status'=>'aktif','created_by'=>$user->id]);
        MaterialSource::create(['project_id'=>$first->id,'code'=>'PSR-01','type'=>'Pasir','name'=>'Pasir Proyek Satu','notes'=>'Catatan satu']);
        MaterialSource::create(['project_id'=>$second->id,'code'=>'BTP-02','type'=>'Batu pecah','name'=>'Kerikil Proyek Dua','notes'=>'Catatan dua']);

        $this->actingAs($user)->get(route('materials.index',['project'=>$first->id]))
            ->assertOk()->assertSee('Pasir Proyek Satu')->assertDontSee('Kerikil Proyek Dua')
            ->assertSee('Filter proyek')->assertSee('Cari kode / nama / catatan')->assertDontSee('Merek');
        $this->get(route('materials.index',['type'=>'Batu pecah']))
            ->assertOk()->assertSee('Kerikil Proyek Dua')->assertDontSee('Pasir Proyek Satu');
    }
}
