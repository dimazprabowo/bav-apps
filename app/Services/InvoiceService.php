<?php

namespace App\Services;

use App\Enums\PaymentApprovalStatus;
use App\Jobs\ProcessInvoiceFile;
use App\Jobs\ProcessInvoicePaymentFile;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Pengadaan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Mengelola Invoice & InvoicePayment sebagai sub-record dari Pengadaan
 * (History/Recurring Records Pattern — dikelola di halaman Detail Pengadaan,
 * bukan menu/permission terpisah, kecuali approve payment yang dedicated).
 */
class InvoiceService
{
    // ============================================================
    // Invoice
    // ============================================================

    public function createInvoice(Pengadaan $pengadaan, array $data, ?array $file = null): Invoice
    {
        return DB::transaction(function () use ($pengadaan, $data, $file) {
            $invoice = $pengadaan->invoices()->create([
                'no_invoice' => $data['no_invoice'],
                'tanggal_invoice' => $data['tanggal_invoice'],
                'jumlah' => $data['jumlah'],
                'jatuh_tempo' => $data['jatuh_tempo'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'file_status' => $file ? 'processing' : null,
            ]);

            if ($file && isset($file['temp_path'])) {
                ProcessInvoiceFile::dispatch(
                    $invoice->id,
                    $file['temp_path'],
                    $file['original_name'],
                    Str::slug($pengadaan->no_pengadaan)
                );
            }

            return $invoice;
        });
    }

    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        $invoice->update([
            'no_invoice' => $data['no_invoice'],
            'tanggal_invoice' => $data['tanggal_invoice'],
            'jumlah' => $data['jumlah'],
            'jatuh_tempo' => $data['jatuh_tempo'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        return $invoice->fresh();
    }

    public function replaceInvoiceFile(Invoice $invoice, array $file, string $pengadaanSlug): void
    {
        app(FileStorageService::class)->delete($invoice->file_path);
        $invoice->update(['file_status' => 'processing', 'file_error' => null]);

        ProcessInvoiceFile::dispatch(
            $invoice->id,
            $file['temp_path'],
            $file['original_name'],
            $pengadaanSlug
        );
    }

    public function deleteInvoice(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            app(FileStorageService::class)->delete($invoice->file_path);

            foreach ($invoice->payments as $payment) {
                app(FileStorageService::class)->delete($payment->file_path);
            }

            $invoice->payments()->delete();
            $invoice->delete();
        });
    }

    // ============================================================
    // Payment
    // ============================================================

    public function createPayment(Invoice $invoice, array $data, ?array $file = null): InvoicePayment
    {
        return DB::transaction(function () use ($invoice, $data, $file) {
            $payment = $invoice->payments()->create([
                'tanggal_bayar' => $data['tanggal_bayar'],
                'jumlah_bayar' => $data['jumlah_bayar'],
                'metode_bayar' => $data['metode_bayar'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'status_approval' => PaymentApprovalStatus::Pending->value,
                'file_status' => $file ? 'processing' : null,
            ]);

            if ($file && isset($file['temp_path'])) {
                ProcessInvoicePaymentFile::dispatch(
                    $payment->id,
                    $file['temp_path'],
                    $file['original_name'],
                    Str::slug($invoice->pengadaan->no_pengadaan)
                );
            }

            return $payment;
        });
    }

    public function updatePayment(InvoicePayment $payment, array $data): InvoicePayment
    {
        $payment->update([
            'tanggal_bayar' => $data['tanggal_bayar'],
            'jumlah_bayar' => $data['jumlah_bayar'],
            'metode_bayar' => $data['metode_bayar'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        return $payment->fresh();
    }

    public function replacePaymentFile(InvoicePayment $payment, array $file, string $pengadaanSlug): void
    {
        app(FileStorageService::class)->delete($payment->file_path);
        $payment->update(['file_status' => 'processing', 'file_error' => null]);

        ProcessInvoicePaymentFile::dispatch(
            $payment->id,
            $file['temp_path'],
            $file['original_name'],
            $pengadaanSlug
        );
    }

    public function deletePayment(InvoicePayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            app(FileStorageService::class)->delete($payment->file_path);
            $payment->delete();
        });
    }

    public function approvePayment(InvoicePayment $payment, int $approverId): InvoicePayment
    {
        return DB::transaction(function () use ($payment, $approverId) {
            $payment->update([
                'status_approval' => PaymentApprovalStatus::Approved->value,
                'approved_by' => $approverId,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            return $payment->fresh();
        });
    }

    public function rejectPayment(InvoicePayment $payment, int $approverId, string $reason): InvoicePayment
    {
        return DB::transaction(function () use ($payment, $approverId, $reason) {
            $payment->update([
                'status_approval' => PaymentApprovalStatus::Rejected->value,
                'approved_by' => $approverId,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $payment->fresh();
        });
    }
}
