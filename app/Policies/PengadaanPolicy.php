<?php

namespace App\Policies;

use App\Enums\PaymentApprovalStatus;
use App\Enums\PengadaanApprovalStatus;
use App\Models\InvoicePayment;
use App\Models\Pengadaan;
use App\Models\User;

class PengadaanPolicy
{
    /**
     * Determine whether the user can access the given pengadaan across cabang boundaries.
     *
     * Users WITHOUT `access_all_cabang` may only interact with pengadaan yang cabang_id-nya
     * cocok dengan cabang mereka sendiri.
     */
    protected function canAccessCabang(User $user, Pengadaan $pengadaan): bool
    {
        if ($user->can('access_all_cabang')) {
            return true;
        }

        return $user->cabang_id !== null && $user->cabang_id === $pengadaan->cabang_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('pengadaan_view');
    }

    public function view(User $user, Pengadaan $pengadaan): bool
    {
        return $user->can('pengadaan_view') && $this->canAccessCabang($user, $pengadaan);
    }

    public function create(User $user): bool
    {
        return $user->can('pengadaan_create');
    }

    public function update(User $user, Pengadaan $pengadaan): bool
    {
        return $user->can('pengadaan_update') && $this->canAccessCabang($user, $pengadaan);
    }

    public function delete(User $user, Pengadaan $pengadaan): bool
    {
        if ($pengadaan->invoices()->exists()) {
            return false;
        }

        return $user->can('pengadaan_delete') && $this->canAccessCabang($user, $pengadaan);
    }

    public function approve(User $user, Pengadaan $pengadaan): bool
    {
        return $user->can('pengadaan_approve')
            && $pengadaan->status_approval === PengadaanApprovalStatus::Pending
            && $this->canAccessCabang($user, $pengadaan);
    }

    /**
     * Approve/reject payment adalah aksi finansial dedicated — TIDAK memakai
     * permission `pengadaan_update` biasa (sesuai rule "Policy untuk Aksi
     * Transisi Status"). Dipanggil via: $this->authorize('approvePayment', [$pengadaan, $payment]).
     */
    public function approvePayment(User $user, Pengadaan $pengadaan, InvoicePayment $payment): bool
    {
        return $user->can('pembayaran_approve')
            && $payment->status_approval === PaymentApprovalStatus::Pending
            && $this->canAccessCabang($user, $pengadaan);
    }

    public function exportExcel(User $user): bool
    {
        return $user->can('pengadaan_export_excel');
    }

    public function exportPdf(User $user): bool
    {
        return $user->can('pengadaan_export_pdf');
    }
}
