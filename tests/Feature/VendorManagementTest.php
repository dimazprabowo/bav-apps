<?php

namespace Tests\Feature;

use App\Livewire\MasterData\VendorManagement;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorManagementTest extends TestCase
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

    public function test_user_without_permission_cannot_view_vendor_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(VendorManagement::class)
            ->assertForbidden();
    }

    public function test_user_with_permission_can_view_vendor_list(): void
    {
        $user = $this->actingUserWithPermissions(['vendor_view']);
        Vendor::factory()->create(['name' => 'PT Contoh Vendor']);

        $this->actingAs($user);

        Livewire::test(VendorManagement::class)
            ->assertSee('PT Contoh Vendor');
    }

    public function test_user_with_permission_can_create_vendor(): void
    {
        $user = $this->actingUserWithPermissions(['vendor_view', 'vendor_create']);

        $this->actingAs($user);

        Livewire::test(VendorManagement::class)
            ->call('create')
            ->set('code', 'vdr-999')
            ->set('name', 'PT Vendor Baru')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('vendors', [
            'code' => 'VDR-999',
            'name' => 'PT Vendor Baru',
        ]);
    }

    public function test_user_without_create_permission_cannot_create_vendor(): void
    {
        $user = $this->actingUserWithPermissions(['vendor_view']);

        $this->actingAs($user);

        Livewire::test(VendorManagement::class)
            ->call('create')
            ->assertForbidden();
    }

    public function test_user_with_permission_can_update_vendor(): void
    {
        $user = $this->actingUserWithPermissions(['vendor_view', 'vendor_update']);
        $vendor = Vendor::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($user);

        Livewire::test(VendorManagement::class)
            ->call('edit', $vendor->id)
            ->set('name', 'Nama Baru')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'name' => 'Nama Baru',
        ]);
    }

    public function test_user_with_permission_can_delete_vendor(): void
    {
        $user = $this->actingUserWithPermissions(['vendor_view', 'vendor_delete']);
        $vendor = Vendor::factory()->create();

        $this->actingAs($user);

        Livewire::test(VendorManagement::class)
            ->call('confirmDelete', $vendor->id)
            ->call('delete');

        $this->assertSoftDeleted('vendors', ['id' => $vendor->id]);
    }

    public function test_code_must_be_unique(): void
    {
        $user = $this->actingUserWithPermissions(['vendor_view', 'vendor_create']);
        Vendor::factory()->create(['code' => 'VDR-100']);

        $this->actingAs($user);

        Livewire::test(VendorManagement::class)
            ->call('create')
            ->set('code', 'VDR-100')
            ->set('name', 'PT Duplikat')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasErrors(['code']);
    }
}
