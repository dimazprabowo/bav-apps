<?php

namespace App\Policies;

use App\Models\Cabang;
use App\Models\User;

class CabangPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cabang_view');
    }

    public function view(User $user, Cabang $cabang): bool
    {
        return $user->can('cabang_view');
    }

    public function create(User $user): bool
    {
        return $user->can('cabang_create');
    }

    public function update(User $user, Cabang $cabang): bool
    {
        return $user->can('cabang_update');
    }

    public function delete(User $user, Cabang $cabang): bool
    {
        if ($cabang->alats()->exists()) {
            return false;
        }

        return $user->can('cabang_delete');
    }

    public function toggleStatus(User $user, Cabang $cabang): bool
    {
        return $user->can('cabang_update');
    }

    public function exportExcel(User $user): bool
    {
        return $user->can('cabang_export_excel');
    }

    public function exportPdf(User $user): bool
    {
        return $user->can('cabang_export_pdf');
    }
}
