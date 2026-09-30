<?php

namespace App\Policies;

use App\Models\Klaster;
use App\Models\User;

class KlasterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('klaster_view');
    }

    public function view(User $user, Klaster $klaster): bool
    {
        return $user->can('klaster_view');
    }

    public function create(User $user): bool
    {
        return $user->can('klaster_create');
    }

    public function update(User $user, Klaster $klaster): bool
    {
        return $user->can('klaster_update');
    }

    public function delete(User $user, Klaster $klaster): bool
    {
        if ($klaster->vendors()->exists()) {
            return false;
        }

        return $user->can('klaster_delete');
    }

    public function toggleStatus(User $user, Klaster $klaster): bool
    {
        return $user->can('klaster_update');
    }

    public function exportExcel(User $user): bool
    {
        return $user->can('klaster_export_excel');
    }

    public function exportPdf(User $user): bool
    {
        return $user->can('klaster_export_pdf');
    }
}
