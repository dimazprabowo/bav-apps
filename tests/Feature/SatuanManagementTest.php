<?php

namespace Tests\Feature;

use App\Livewire\MasterData\SatuanManagement;
use App\Models\Pengadaan;
use App\Models\PengadaanItem;
use App\Models\Satuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SatuanManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUserWithPermissions(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'test-role', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_user_without_permission_cannot_view_satuan_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(SatuanManagement::class)
            ->assertForbidden();
    }

    public function test_user_with_permission_can_view_satuan_list(): void
    {
        $user = $this->actingUserWithPermissions(['satuan_view']);
        Satuan::factory()->create(['name' => 'Kilogram']);

        $this->actingAs($user);

        Livewire::test(SatuanManagement::class)
            ->assertSee('Kilogram');
    }

    public function test_user_with_permission_can_create_satuan(): void
    {
        $user = $this->actingUserWithPermissions(['satuan_view', 'satuan_create']);

        $this->actingAs($user);

        Livewire::test(SatuanManagement::class)
            ->call('create')
            ->set('code', 'pcs')
            ->set('name', 'Pieces')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('satuans', [
            'code' => 'PCS',
            'name' => 'Pieces',
        ]);
    }

    public function test_user_without_create_permission_cannot_create_satuan(): void
    {
        $user = $this->actingUserWithPermissions(['satuan_view']);

        $this->actingAs($user);

        Livewire::test(SatuanManagement::class)
            ->call('create')
            ->assertForbidden();
    }

    public function test_user_with_permission_can_update_satuan(): void
    {
        $user = $this->actingUserWithPermissions(['satuan_view', 'satuan_update']);
        $satuan = Satuan::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($user);

        Livewire::test(SatuanManagement::class)
            ->call('edit', $satuan->id)
            ->set('name', 'Nama Baru')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('satuans', [
            'id' => $satuan->id,
            'name' => 'Nama Baru',
        ]);
    }

    public function test_user_with_permission_can_delete_unused_satuan(): void
    {
        $user = $this->actingUserWithPermissions(['satuan_view', 'satuan_delete']);
        $satuan = Satuan::factory()->create();

        $this->actingAs($user);

        Livewire::test(SatuanManagement::class)
            ->call('confirmDelete', $satuan->id)
            ->call('delete');

        $this->assertSoftDeleted('satuans', ['id' => $satuan->id]);
    }

    public function test_satuan_used_by_pengadaan_item_cannot_be_deleted(): void
    {
        $user = $this->actingUserWithPermissions(['satuan_view', 'satuan_delete']);
        $satuan = Satuan::factory()->create();
        PengadaanItem::factory()->create([
            'pengadaan_id' => Pengadaan::factory()->create()->id,
            'satuan_id' => $satuan->id,
        ]);

        $this->actingAs($user);

        Livewire::test(SatuanManagement::class)
            ->call('confirmDelete', $satuan->id)
            ->call('delete')
            ->assertNotDispatched('notifySuccess');

        $this->assertDatabaseHas('satuans', [
            'id' => $satuan->id,
            'deleted_at' => null,
        ]);
    }

    public function test_code_must_be_unique(): void
    {
        $user = $this->actingUserWithPermissions(['satuan_view', 'satuan_create']);
        Satuan::factory()->create(['code' => 'UNIT']);

        $this->actingAs($user);

        Livewire::test(SatuanManagement::class)
            ->call('create')
            ->set('code', 'UNIT')
            ->set('name', 'Satuan Duplikat')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasErrors(['code']);
    }
}
