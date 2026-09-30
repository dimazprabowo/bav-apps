<?php

namespace Tests\Feature;

use App\Livewire\MasterData\KategoriItemManagement;
use App\Models\KategoriItem;
use App\Models\Pengadaan;
use App\Models\PengadaanItem;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KategoriItemManagementTest extends TestCase
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

    public function test_user_without_permission_cannot_view_kategori_item_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(KategoriItemManagement::class)
            ->assertForbidden();
    }

    public function test_user_with_permission_can_view_kategori_item_list(): void
    {
        $user = $this->actingUserWithPermissions(['kategori_item_view']);
        KategoriItem::factory()->create(['name' => 'Alat Ukur Listrik']);

        $this->actingAs($user);

        Livewire::test(KategoriItemManagement::class)
            ->assertSee('Alat Ukur Listrik');
    }

    public function test_user_with_permission_can_create_kategori_item(): void
    {
        $user = $this->actingUserWithPermissions(['kategori_item_view', 'kategori_item_create']);

        $this->actingAs($user);

        Livewire::test(KategoriItemManagement::class)
            ->call('create')
            ->set('code', 'ktg-001')
            ->set('name', 'Alat Ukur')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kategori_items', [
            'code' => 'KTG-001',
            'name' => 'Alat Ukur',
        ]);
    }

    public function test_user_without_create_permission_cannot_create_kategori_item(): void
    {
        $user = $this->actingUserWithPermissions(['kategori_item_view']);

        $this->actingAs($user);

        Livewire::test(KategoriItemManagement::class)
            ->call('create')
            ->assertForbidden();
    }

    public function test_user_with_permission_can_update_kategori_item(): void
    {
        $user = $this->actingUserWithPermissions(['kategori_item_view', 'kategori_item_update']);
        $kategoriItem = KategoriItem::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($user);

        Livewire::test(KategoriItemManagement::class)
            ->call('edit', $kategoriItem->id)
            ->set('name', 'Nama Baru')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kategori_items', [
            'id' => $kategoriItem->id,
            'name' => 'Nama Baru',
        ]);
    }

    public function test_user_with_permission_can_delete_unused_kategori_item(): void
    {
        $user = $this->actingUserWithPermissions(['kategori_item_view', 'kategori_item_delete']);
        $kategoriItem = KategoriItem::factory()->create();

        $this->actingAs($user);

        Livewire::test(KategoriItemManagement::class)
            ->call('confirmDelete', $kategoriItem->id)
            ->call('delete');

        $this->assertSoftDeleted('kategori_items', ['id' => $kategoriItem->id]);
    }

    public function test_kategori_item_used_by_pengadaan_item_cannot_be_deleted(): void
    {
        $user = $this->actingUserWithPermissions(['kategori_item_view', 'kategori_item_delete']);
        $kategoriItem = KategoriItem::factory()->create();
        PengadaanItem::factory()->create([
            'pengadaan_id' => Pengadaan::factory()->create()->id,
            'kategori_item_id' => $kategoriItem->id,
        ]);

        $this->actingAs($user);

        Livewire::test(KategoriItemManagement::class)
            ->call('confirmDelete', $kategoriItem->id)
            ->call('delete')
            ->assertNotDispatched('notifySuccess');

        $this->assertDatabaseHas('kategori_items', [
            'id' => $kategoriItem->id,
            'deleted_at' => null,
        ]);
    }

    public function test_kategori_item_linked_to_vendor_cannot_be_deleted(): void
    {
        $user = $this->actingUserWithPermissions(['kategori_item_view', 'kategori_item_delete']);
        $kategoriItem = KategoriItem::factory()->create();
        Vendor::factory()->create()->kategoriItems()->attach($kategoriItem->id);

        $this->actingAs($user);

        Livewire::test(KategoriItemManagement::class)
            ->call('confirmDelete', $kategoriItem->id)
            ->call('delete')
            ->assertNotDispatched('notifySuccess');

        $this->assertDatabaseHas('kategori_items', [
            'id' => $kategoriItem->id,
            'deleted_at' => null,
        ]);
    }

    public function test_code_must_be_unique(): void
    {
        $user = $this->actingUserWithPermissions(['kategori_item_view', 'kategori_item_create']);
        KategoriItem::factory()->create(['code' => 'KTG-001']);

        $this->actingAs($user);

        Livewire::test(KategoriItemManagement::class)
            ->call('create')
            ->set('code', 'KTG-001')
            ->set('name', 'Kategori Duplikat')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasErrors(['code']);
    }
}
