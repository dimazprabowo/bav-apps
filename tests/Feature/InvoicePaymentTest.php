<?php

namespace Tests\Feature;

use App\Livewire\Pengadaan\PengadaanDetail;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Pengadaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUserWithPermissions(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        Permission::firstOrCreate(['name' => 'access_all_cabang', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'test-role-invoice-'.implode('-', $permissions), 'guard_name' => 'web']);
        $role->syncPermissions(array_merge($permissions, ['access_all_cabang']));

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_user_can_create_invoice_for_pengadaan(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_update']);
        $pengadaan = Pengadaan::factory()->create();

        $this->actingAs($user);

        Livewire::test(PengadaanDetail::class, ['pengadaan' => $pengadaan])
            ->call('openCreateInvoiceModal')
            ->set('inv_no_invoice', 'INV-001')
            ->set('inv_tanggal_invoice', now()->format('Y-m-d'))
            ->set('inv_jumlah', '5000000')
            ->call('saveInvoice')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoices', [
            'pengadaan_id' => $pengadaan->id,
            'no_invoice' => 'INV-001',
            'jumlah' => 5000000,
        ]);
    }

    public function test_status_invoice_derived_correctly(): void
    {
        $pengadaan = Pengadaan::factory()->create(['total_biaya' => 10000000]);
        Invoice::factory()->create(['pengadaan_id' => $pengadaan->id, 'jumlah' => 4000000]);

        $pengadaan->load('invoices');

        $this->assertEquals('ditagih_sebagian', $pengadaan->status_invoice->value);
    }

    public function test_status_pembayaran_only_counts_approved_payments(): void
    {
        $pengadaan = Pengadaan::factory()->create();
        $invoice = Invoice::factory()->create(['pengadaan_id' => $pengadaan->id, 'jumlah' => 1000000]);

        // Pending payment should NOT count towards "sudah dibayar"
        InvoicePayment::factory()->create(['invoice_id' => $invoice->id, 'jumlah_bayar' => 1000000]);

        $invoice->refresh()->load('payments');
        $this->assertEquals('belum_dibayar', $invoice->status_pembayaran->value);

        // Approve it -> should now be Lunas
        InvoicePayment::where('invoice_id', $invoice->id)->first()->update([
            'status_approval' => 'approved',
            'approved_at' => now(),
        ]);

        $invoice->refresh()->load('payments');
        $this->assertEquals('lunas', $invoice->status_pembayaran->value);
    }

    public function test_user_with_pembayaran_approve_can_approve_payment(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pembayaran_approve']);
        $pengadaan = Pengadaan::factory()->create();
        $invoice = Invoice::factory()->create(['pengadaan_id' => $pengadaan->id]);
        $payment = InvoicePayment::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($user);

        Livewire::test(PengadaanDetail::class, ['pengadaan' => $pengadaan])
            ->call('confirmApprovePayment', $payment->id)
            ->call('approvePayment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoice_payments', [
            'id' => $payment->id,
            'status_approval' => 'approved',
            'approved_by' => $user->id,
        ]);
    }

    public function test_user_without_pembayaran_approve_permission_cannot_approve_payment(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_update']);
        $pengadaan = Pengadaan::factory()->create();
        $invoice = Invoice::factory()->create(['pengadaan_id' => $pengadaan->id]);
        $payment = InvoicePayment::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($user);

        Livewire::test(PengadaanDetail::class, ['pengadaan' => $pengadaan])
            ->call('confirmApprovePayment', $payment->id)
            ->assertForbidden();
    }

    public function test_user_with_pembayaran_approve_can_reject_payment(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pembayaran_approve']);
        $pengadaan = Pengadaan::factory()->create();
        $invoice = Invoice::factory()->create(['pengadaan_id' => $pengadaan->id]);
        $payment = InvoicePayment::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($user);

        Livewire::test(PengadaanDetail::class, ['pengadaan' => $pengadaan])
            ->call('confirmRejectPayment', $payment->id)
            ->set('pay_rejection_reason', 'Bukti transfer tidak valid')
            ->call('rejectPayment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoice_payments', [
            'id' => $payment->id,
            'status_approval' => 'rejected',
            'rejection_reason' => 'Bukti transfer tidak valid',
        ]);
    }

    public function test_cannot_approve_already_approved_payment(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pembayaran_approve']);
        $pengadaan = Pengadaan::factory()->create();
        $invoice = Invoice::factory()->create(['pengadaan_id' => $pengadaan->id]);
        $payment = InvoicePayment::factory()->approved()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($user);

        Livewire::test(PengadaanDetail::class, ['pengadaan' => $pengadaan])
            ->call('confirmApprovePayment', $payment->id)
            ->assertForbidden();
    }

    public function test_pengadaan_cannot_be_deleted_when_it_has_invoices(): void
    {
        $user = $this->actingUserWithPermissions(['pengadaan_view', 'pengadaan_delete']);
        $pengadaan = Pengadaan::factory()->create();
        Invoice::factory()->create(['pengadaan_id' => $pengadaan->id]);

        $this->assertFalse($user->can('delete', $pengadaan));
    }
}
