<?php

namespace Tests\Feature;

use App\Livewire\MasterData\KlasterManagement;
use App\Models\Klaster;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KlasterManagementTest extends TestCase
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

    public function test_user_without_permission_cannot_view_klaster_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(KlasterManagement::class)
            ->assertForbidden();
    }

    public function test_user_with_permission_can_view_klaster_list(): void
    {
        $user = $this->actingUserWithPermissions(['klaster_view']);
        Klaster::factory()->create(['name' => 'Alat Ukur']);

        $this->actingAs($user);

        Livewire::test(KlasterManagement::class)
            ->assertSee('Alat Ukur');
    }

    public function test_user_with_permission_can_create_klaster(): void
    {
        $user = $this->actingUserWithPermissions(['klaster_view', 'klaster_create']);

        $this->actingAs($user);

        Livewire::test(KlasterManagement::class)
            ->call('create')
            ->set('code', 'kls-999')
            ->set('name', 'Klaster Baru')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('klasters', [
            'code' => 'KLS-999',
            'name' => 'Klaster Baru',
        ]);
    }

    public function test_user_without_create_permission_cannot_create_klaster(): void
    {
        $user = $this->actingUserWithPermissions(['klaster_view']);

        $this->actingAs($user);

        Livewire::test(KlasterManagement::class)
            ->call('create')
            ->assertForbidden();
    }

    public function test_user_with_permission_can_update_klaster(): void
    {
        $user = $this->actingUserWithPermissions(['klaster_view', 'klaster_update']);
        $klaster = Klaster::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($user);

        Livewire::test(KlasterManagement::class)
            ->call('edit', $klaster->id)
            ->set('name', 'Nama Baru')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('klasters', [
            'id' => $klaster->id,
            'name' => 'Nama Baru',
        ]);
    }

    public function test_user_with_permission_can_delete_empty_klaster(): void
    {
        $user = $this->actingUserWithPermissions(['klaster_view', 'klaster_delete']);
        $klaster = Klaster::factory()->create();

        $this->actingAs($user);

        Livewire::test(KlasterManagement::class)
            ->call('confirmDelete', $klaster->id)
            ->call('delete');

        $this->assertSoftDeleted('klasters', ['id' => $klaster->id]);
    }

    public function test_klaster_with_vendors_cannot_be_deleted(): void
    {
        $user = $this->actingUserWithPermissions(['klaster_view', 'klaster_delete']);
        $klaster = Klaster::factory()->create();
        Vendor::factory()->create(['klaster_id' => $klaster->id]);

        $this->actingAs($user);

        Livewire::test(KlasterManagement::class)
            ->call('confirmDelete', $klaster->id)
            ->call('delete')
            ->assertNotDispatched('notifySuccess');

        $this->assertDatabaseHas('klasters', [
            'id' => $klaster->id,
            'deleted_at' => null,
        ]);
    }

    public function test_code_must_be_unique(): void
    {
        $user = $this->actingUserWithPermissions(['klaster_view', 'klaster_create']);
        Klaster::factory()->create(['code' => 'KLS-100']);

        $this->actingAs($user);

        Livewire::test(KlasterManagement::class)
            ->call('create')
            ->set('code', 'KLS-100')
            ->set('name', 'Klaster Duplikat')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasErrors(['code']);
    }
}
