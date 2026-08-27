<?php

namespace App\Policies;

use App\Enums\AlatReviewStatus;
use App\Models\Alat;
use App\Models\User;

class AlatPolicy
{
    /**
     * Determine whether the user can access the given alat across cabang boundaries.
     *
     * Users WITHOUT `access_all_cabang` may only interact with alat in their own cabang.
     */
    protected function canAccessCabang(User $user, Alat $alat): bool
    {
        if ($user->can('access_all_cabang')) {
            return true;
        }

        return $user->cabang_id !== null && $user->cabang_id === $alat->cabang_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('alat_view');
    }

    public function view(User $user, Alat $alat): bool
    {
        return $user->can('alat_view') && $this->canAccessCabang($user, $alat);
    }

    public function create(User $user): bool
    {
        return $user->can('alat_create');
    }

    public function update(User $user, Alat $alat): bool
    {
        return $user->can('alat_update') && $this->canAccessCabang($user, $alat);
    }

    public function delete(User $user, Alat $alat): bool
    {
        if ($alat->logBookPeminjaman()->whereIn('status', ['borrowed', 'overdue'])->exists()) {
            return false;
        }

        return $user->can('alat_delete') && $this->canAccessCabang($user, $alat);
    }

    public function review(User $user, Alat $alat): bool
    {
        return $user->can('alat_review')
            && $alat->review_status === AlatReviewStatus::Pending
            && $this->canAccessCabang($user, $alat);
    }

    public function toggleStatus(User $user, Alat $alat): bool
    {
        return $user->can('alat_update') && $this->canAccessCabang($user, $alat);
    }

    public function exportExcel(User $user): bool
    {
        return $user->can('alat_export_excel');
    }

    public function exportPdf(User $user): bool
    {
        return $user->can('alat_export_pdf');
    }
}
