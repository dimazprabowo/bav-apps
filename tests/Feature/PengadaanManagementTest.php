<?php

namespace Tests\Feature;

use App\Livewire\Pengadaan\PengadaanForm;
use App\Livewire\Pengadaan\PengadaanManagement;
use App\Models\Pengadaan;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PengadaanManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUserWithPermissions(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        Permission::firstOrCreate(['name' => 'access_all_cabang', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'test-role-pengadaan', 'guard_name' => 'web']);
        $role->syncPermissions(array_merge($permissions, ['access_all_cabang']));

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_user_without_permission_cannot_view_pengadaan_list(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(PengadaanManagement::class)->assertForbidden();
    }

    public function test_user_with_permission_can_view_pengadaan_list(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view']);
        $pengadaan = Pengadaan::factory()->create(['no_pengadaan' => 'PG-TEST-001']);

        $this->actingAs($user);

        Livewire::test(PengadaanManagement::class)
            ->assertSee('PG-TEST-001');
    }

    public function test_user_can_create_pengadaan_with_items(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_create']);
        $vendor = Vendor::factory()->create();

        $this->actingAs($user);

        Livewire::test(PengadaanForm::class)
            ->set('no_pengadaan', 'pg-2026-001')
            ->set('klaster_id', $vendor->klaster_id)
            ->set('vendor_id', $vendor->id)
            ->set('tanggal_pengadaan', now()->format('Y-m-d'))
            ->set('items', [
                ['id' => null, 'alat_id' => null, 'nama_aset' => 'Laptop', 'kategori_aset' => 'IT', 'qty' => 2, 'satuan' => 'unit', 'harga_satuan' => '10000000'],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pengadaans', [
            'no_pengadaan' => 'PG-2026-001',
            'vendor_id' => $vendor->id,
            'total_biaya' => 20000000,
        ]);

        $pengadaan = Pengadaan::where('no_pengadaan', 'PG-2026-001')->first();
        $this->assertDatabaseHas('pengadaan_items', [
            'pengadaan_id' => $pengadaan->id,
            'nama_aset' => 'Laptop',
            'qty' => 2,
            'subtotal' => 20000000,
        ]);
    }

    public function test_vendor_options_filtered_by_selected_klaster(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_create']);
        $klasterA = \App\Models\Klaster::factory()->create();
        $klasterB = \App\Models\Klaster::factory()->create();
        $vendorA = Vendor::factory()->create(['klaster_id' => $klasterA->id]);
        Vendor::factory()->create(['klaster_id' => $klasterB->id]);

        $this->actingAs($user);

        $component = Livewire::test(PengadaanForm::class);
        $this->assertEmpty($component->instance()->vendorOptions);

        $component->set('klaster_id', $klasterA->id)->assertSet('vendor_id', null);

        $options = collect($component->instance()->vendorOptions);
        $this->assertEquals([$vendorA->id], $options->pluck('value')->all());
    }

    public function test_user_without_create_permission_cannot_create_pengadaan(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view']);
        $this->actingAs($user);

        Livewire::test(PengadaanForm::class)->assertForbidden();
    }

    public function test_user_with_approve_permission_can_approve_pending_pengadaan(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_approve']);
        $pengadaan = Pengadaan::factory()->create();

        $this->actingAs($user);

        Livewire::test(PengadaanManagement::class)
            ->call('confirmApprove', $pengadaan->id)
            ->call('approve')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pengadaans', [
            'id' => $pengadaan->id,
            'status_approval' => 'approved',
            'approved_by' => $user->id,
        ]);
    }

    public function test_user_with_approve_permission_can_reject_pending_pengadaan(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_approve']);
        $pengadaan = Pengadaan::factory()->create();

        $this->actingAs($user);

        Livewire::test(PengadaanManagement::class)
            ->call('confirmReject', $pengadaan->id)
            ->set('rejectionReason', 'Harga tidak sesuai anggaran')
            ->call('reject')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pengadaans', [
            'id' => $pengadaan->id,
            'status_approval' => 'rejected',
            'rejection_reason' => 'Harga tidak sesuai anggaran',
        ]);
    }

    public function test_cannot_approve_already_approved_pengadaan(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_approve']);
        $pengadaan = Pengadaan::factory()->approved()->create();

        $this->actingAs($user);

        Livewire::test(PengadaanManagement::class)
            ->call('confirmApprove', $pengadaan->id)
            ->assertForbidden();
    }

    public function test_user_with_permission_can_delete_pengadaan(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_delete']);
        $pengadaan = Pengadaan::factory()->create();

        $this->actingAs($user);

        Livewire::test(PengadaanManagement::class)
            ->call('confirmDelete', $pengadaan->id)
            ->call('delete');

        $this->assertSoftDeleted('pengadaans', ['id' => $pengadaan->id]);
    }
}
