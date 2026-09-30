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
        $kategoriItem = \App\Models\KategoriItem::factory()->create();
        $vendor->kategoriItems()->sync([$kategoriItem->id]);

        $this->actingAs($user);

        Livewire::test(PengadaanForm::class)
            ->set('no_pengadaan', 'pg-2026-001')
            ->set('nama_pemohon', 'Budi Santoso')
            ->set('tipe_biaya', 'Fix Cost')
            ->set('klaster_id', $vendor->klaster_id)
            ->set('vendor_id', $vendor->id)
            ->set('tanggal_pengadaan', now()->format('Y-m-d'))
            ->set('items', [
                ['id' => null, 'nama_item' => 'Laptop', 'kategori_item_id' => $kategoriItem->id, 'qty' => 2, 'satuan_id' => \App\Models\Satuan::factory()->create()->id, 'harga_satuan' => '10000000'],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pengadaans', [
            'no_pengadaan' => 'PG-2026-001',
            'nama_pemohon' => 'Budi Santoso',
            'tipe_biaya' => 'Fix Cost',
            'no_wbs' => null,
            'vendor_id' => $vendor->id,
            'total_biaya' => 20000000,
        ]);

        $pengadaan = Pengadaan::where('no_pengadaan', 'PG-2026-001')->first();
        $this->assertDatabaseHas('pengadaan_items', [
            'pengadaan_id' => $pengadaan->id,
            'nama_item' => 'Laptop',
            'kategori_item_id' => $kategoriItem->id,
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

    public function test_kategori_item_options_filtered_by_selected_vendor(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_create']);
        $vendorA = Vendor::factory()->create();
        $vendorB = Vendor::factory()->create();
        $kategoriA = \App\Models\KategoriItem::factory()->create();
        $kategoriB = \App\Models\KategoriItem::factory()->create();
        $vendorA->kategoriItems()->sync([$kategoriA->id]);
        $vendorB->kategoriItems()->sync([$kategoriB->id]);

        $this->actingAs($user);

        $component = Livewire::test(PengadaanForm::class);
        $this->assertEmpty($component->instance()->kategoriItemOptions);

        $component->set('vendor_id', $vendorA->id);

        $options = collect($component->instance()->kategoriItemOptions);
        $this->assertEquals([$kategoriA->id], $options->pluck('value')->all());
    }

    public function test_item_kategori_must_belong_to_selected_vendor(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_create']);
        $vendor = Vendor::factory()->create();
        $otherKategori = \App\Models\KategoriItem::factory()->create();

        $this->actingAs($user);

        Livewire::test(PengadaanForm::class)
            ->set('no_pengadaan', 'PG-2026-002')
            ->set('nama_pemohon', 'Andi Wijaya')
            ->set('tipe_biaya', 'Fix Cost')
            ->set('klaster_id', $vendor->klaster_id)
            ->set('vendor_id', $vendor->id)
            ->set('tanggal_pengadaan', now()->format('Y-m-d'))
            ->set('items', [
                ['id' => null, 'nama_item' => 'Laptop', 'kategori_item_id' => $otherKategori->id, 'qty' => 1, 'satuan_id' => \App\Models\Satuan::factory()->create()->id, 'harga_satuan' => '10000000'],
            ])
            ->call('save')
            ->assertHasErrors(['items.0.kategori_item_id']);
    }

    public function test_no_wbs_required_when_tipe_biaya_rab_project(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_create']);
        $vendor = Vendor::factory()->create();

        $this->actingAs($user);

        Livewire::test(PengadaanForm::class)
            ->set('no_pengadaan', 'PG-2026-003')
            ->set('nama_pemohon', 'Siti Rahayu')
            ->set('tipe_biaya', 'RAB Project')
            ->set('klaster_id', $vendor->klaster_id)
            ->set('vendor_id', $vendor->id)
            ->set('tanggal_pengadaan', now()->format('Y-m-d'))
            ->set('items', [
                ['id' => null, 'nama_item' => 'Laptop', 'kategori_item_id' => null, 'qty' => 1, 'satuan_id' => \App\Models\Satuan::factory()->create()->id, 'harga_satuan' => '5000000'],
            ])
            ->call('save')
            ->assertHasErrors(['no_wbs'])
            ->set('no_wbs', 'wbs-prj-001')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pengadaans', [
            'no_pengadaan' => 'PG-2026-003',
            'tipe_biaya' => 'RAB Project',
            'no_wbs' => 'WBS-PRJ-001',
        ]);
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
