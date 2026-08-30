<?php

namespace App\Policies;

use App\Enums\LogBookStatus;
use App\Models\LogBookPeminjaman;
use App\Models\User;

class LogBookPeminjamanPolicy
{
    /**
     * Determine whether the user can access (view/edit) the given logbook.
     *
     * Users WITHOUT `access_all_cabang` may access a logbook if:
     *   - the alat's cabang matches their own cabang, OR
     *   - they are the peminjam (their own request, even cross-cabang).
     */
    protected function canAccess(User $user, LogBookPeminjaman $logBook): bool
    {
        if ($user->can('access_all_cabang')) {
            return true;
        }

        if ($user->id === $logBook->peminjam_id) {
            return true;
        }

        return $user->cabang_id !== null && $user->cabang_id === $logBook->cabang_id;
    }

    /**
     * Determine whether the user can manage (approve/return/start) the logbook.
     *
     * Stricter than canAccess: peminjam match is NOT enough — these are
     * operational/admin actions scoped to the alat's cabang.
     */
    protected function canManage(User $user, LogBookPeminjaman $logBook): bool
    {
        if ($user->can('access_all_cabang')) {
            return true;
        }

        return $user->cabang_id !== null && $user->cabang_id === $logBook->cabang_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('logbook_view');
    }

    public function view(User $user, LogBookPeminjaman $logBook): bool
    {
        return $user->can('logbook_view') && $this->canAccess($user, $logBook);
    }

    public function create(User $user): bool
    {
        return $user->can('logbook_create');
    }

    public function update(User $user, LogBookPeminjaman $logBook): bool
    {
        return $user->can('logbook_update')
            && $logBook->status === LogBookStatus::Requested
            && $this->canAccess($user, $logBook);
    }

    public function delete(User $user, LogBookPeminjaman $logBook): bool
    {
        return $user->can('logbook_delete')
            && $logBook->status === LogBookStatus::Requested
            && $this->canAccess($user, $logBook);
    }

    public function approve(User $user, LogBookPeminjaman $logBook): bool
    {
        return $user->can('logbook_approve')
            && $logBook->status === LogBookStatus::Requested
            && $this->canManage($user, $logBook);
    }

    public function returnAlat(User $user, LogBookPeminjaman $logBook): bool
    {
        return $user->can('logbook_return')
            && in_array($logBook->status, [LogBookStatus::Borrowed, LogBookStatus::Overdue])
            && $this->canManage($user, $logBook);
    }

    public function startBorrowing(User $user, LogBookPeminjaman $logBook): bool
    {
        return $user->can('logbook_approve')
            && $logBook->status === LogBookStatus::Approved
            && $this->canManage($user, $logBook);
    }

    /**
     * Peminjam dapat membatalkan request sendiri selama belum dipinjam (Requested/Approved).
     * Admin cabang/pusat dapat membatalkan request di scope-nya.
     */
    public function cancel(User $user, LogBookPeminjaman $logBook): bool
    {
        return $user->can('logbook_cancel')
            && in_array($logBook->status, [LogBookStatus::Requested, LogBookStatus::Approved])
            && $this->canAccess($user, $logBook);
    }

    public function exportExcel(User $user): bool
    {
        return $user->can('logbook_export_excel');
    }

    public function exportPdf(User $user): bool
    {
        return $user->can('logbook_export_pdf');
    }
}
