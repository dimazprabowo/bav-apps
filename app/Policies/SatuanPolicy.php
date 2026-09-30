<?php

namespace App\Policies;

use App\Models\Satuan;
use App\Models\User;

class SatuanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('satuan_view');
    }

    public function view(User $user, Satuan $satuan): bool
    {
        return $user->can('satuan_view');
    }

    public function create(User $user): bool
    {
        return $user->can('satuan_create');
    }

    public function update(User $user, Satuan $satuan): bool
    {
        return $user->can('satuan_update');
    }

    public function delete(User $user, Satuan $satuan): bool
    {
        if ($satuan->pengadaanItems()->exists()) {
            return false;
        }

        return $user->can('satuan_delete');
    }

    public function toggleStatus(User $user, Satuan $satuan): bool
    {
        return $user->can('satuan_update');
    }

    public function exportExcel(User $user): bool
    {
        return $user->can('satuan_export_excel');
    }

    public function exportPdf(User $user): bool
    {
        return $user->can('satuan_export_pdf');
    }
}
