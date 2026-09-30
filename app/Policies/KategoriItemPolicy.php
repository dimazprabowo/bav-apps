<?php

namespace App\Policies;

use App\Models\KategoriItem;
use App\Models\User;

class KategoriItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('kategori_item_view');
    }

    public function view(User $user, KategoriItem $kategoriItem): bool
    {
        return $user->can('kategori_item_view');
    }

    public function create(User $user): bool
    {
        return $user->can('kategori_item_create');
    }

    public function update(User $user, KategoriItem $kategoriItem): bool
    {
        return $user->can('kategori_item_update');
    }

    public function delete(User $user, KategoriItem $kategoriItem): bool
    {
        if (! $user->can('kategori_item_delete')) {
            return false;
        }

        if ($kategoriItem->vendors()->exists() || $kategoriItem->pengadaanItems()->exists()) {
            return false;
        }

        return true;
    }

    public function toggleStatus(User $user, KategoriItem $kategoriItem): bool
    {
        return $user->can('kategori_item_update');
    }

    public function exportExcel(User $user): bool
    {
        return $user->can('kategori_item_export_excel');
    }

    public function exportPdf(User $user): bool
    {
        return $user->can('kategori_item_export_pdf');
    }
}
