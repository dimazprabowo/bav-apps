<?php

namespace Tests\Feature;

use App\Livewire\Pengadaan\PengadaanManagement;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Pengadaan;
use App\Models\User;
use App\Models\Vendor;
use App\Services\PengadaanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PengadaanDashboardExportTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUserWithPermissions(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        Permission::firstOrCreate(['name' => 'access_all_cabang', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'test-role-dashboard-'.implode('-', $permissions), 'guard_name' => 'web']);
        $role->syncPermissions(array_merge($permissions, ['access_all_cabang']));

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_dashboard_stats_calculates_total_biaya_invoice_and_outstanding(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view']);
        $this->actingAs($user);

        $pengadaan = Pengadaan::factory()->approved()->create(['total_biaya' => 10000000]);
        $invoice = Invoice::factory()->create(['pengadaan_id' => $pengadaan->id, 'jumlah' => 6000000]);
        InvoicePayment::factory()->approved()->create(['invoice_id' => $invoice->id, 'jumlah_bayar' => 4000000]);

        // Pengadaan pending (belum approved) TIDAK dihitung.
        Pengadaan::factory()->create(['total_biaya' => 99999999]);

        $stats = app(PengadaanService::class)->getDashboardStats();

        $this->assertEquals(10000000, $stats['total_biaya']);
        $this->assertEquals(6000000, $stats['total_invoice']);
        $this->assertEquals(4000000, $stats['total_belum_ditagih']);
        $this->assertEquals(4000000, $stats['total_dibayar']);
        $this->assertEquals(2000000, $stats['total_outstanding']);
    }

    public function test_get_spend_per_vendor_returns_top_vendors(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view']);
        $this->actingAs($user);

        $vendorA = Vendor::factory()->create(['name' => 'PT Vendor A']);
        $vendorB = Vendor::factory()->create(['name' => 'PT Vendor B']);

        Pengadaan::factory()->approved()->create(['vendor_id' => $vendorA->id, 'total_biaya' => 5000000]);
        Pengadaan::factory()->approved()->create(['vendor_id' => $vendorB->id, 'total_biaya' => 15000000]);

        $result = app(PengadaanService::class)->getSpendPerVendor();

        $this->assertEquals('PT Vendor B', $result[0]['vendor']);
        $this->assertEquals(15000000, $result[0]['total_spend']);
    }

    public function test_user_without_export_permission_cannot_export_excel(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view']);
        $this->actingAs($user);

        Livewire::test(PengadaanManagement::class)
            ->call('exportExcel')
            ->assertForbidden();
    }

    public function test_user_with_export_permission_can_export_excel(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_export_excel']);
        Pengadaan::factory()->create();

        $this->actingAs($user);

        Livewire::test(PengadaanManagement::class)
            ->call('exportExcel')
            ->assertFileDownloaded();
    }

    public function test_user_without_export_pdf_permission_cannot_export_pdf(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view']);
        $this->actingAs($user);

        Livewire::test(PengadaanManagement::class)
            ->call('exportPdf')
            ->assertForbidden();
    }
}
