<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionService
{
    public function getAllRolesWithPermissions(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }

    public function getRolePermissions(int $roleId): array
    {
        $role = Role::with('permissions')->find($roleId);

        return $role ? $role->permissions->pluck('name')->toArray() : [];
    }

    public function createRole(string $name, array $permissions = []): Role
    {
        $role = Role::create(['name' => $name]);
        $role->syncPermissions($permissions);

        return $role;
    }

    public function updateRole(Role $role, string $name, array $permissions = []): Role
    {
        $role->update(['name' => $name]);
        $role->syncPermissions($permissions);

        return $role;
    }

    public function deleteRole(Role $role): void
    {
        $role->delete();
    }

    public function togglePermission(Role $role, string $permission): string
    {
        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);

            return 'revoked';
        }

        $role->givePermissionTo($permission);

        return 'granted';
    }

    public function roleHasUsers(Role $role): bool
    {
        return $role->users()->count() > 0;
    }

    /**
     * Build permission groups dynamically from database.
     * Maps permissions to groups based on naming convention.
     * Any unmatched permissions go to 'Lainnya' group.
     *
     * Returns: ['Group Name' => [['name' => 'permission_key', 'label' => 'Human Label'], ...]]
     */
    public function buildPermissionGroups(): array
    {
        $groupMapping = [
            'Dashboard' => [
                ['name' => 'dashboard_view', 'label' => 'Lihat Dashboard'],
            ],
            'Cabang' => [
                ['name' => 'cabang_view',         'label' => 'Lihat Cabang'],
                ['name' => 'cabang_create',       'label' => 'Tambah Cabang'],
                ['name' => 'cabang_update',       'label' => 'Edit Cabang'],
                ['name' => 'cabang_delete',       'label' => 'Hapus Cabang'],
                ['name' => 'cabang_export_excel', 'label' => 'Export Excel Cabang'],
                ['name' => 'cabang_export_pdf',   'label' => 'Export PDF Cabang'],
            ],
            'Klaster' => [
                ['name' => 'klaster_view',         'label' => 'Lihat Klaster'],
                ['name' => 'klaster_create',       'label' => 'Tambah Klaster'],
                ['name' => 'klaster_update',       'label' => 'Edit Klaster'],
                ['name' => 'klaster_delete',       'label' => 'Hapus Klaster'],
                ['name' => 'klaster_export_excel', 'label' => 'Export Excel Klaster'],
                ['name' => 'klaster_export_pdf',   'label' => 'Export PDF Klaster'],
            ],
            'Pengadaan Aset' => [
                ['name' => 'vendor_view',         'label' => 'Lihat Vendor'],
                ['name' => 'vendor_create',       'label' => 'Tambah Vendor'],
                ['name' => 'vendor_update',       'label' => 'Edit Vendor'],
                ['name' => 'vendor_delete',       'label' => 'Hapus Vendor'],
                ['name' => 'vendor_export_excel', 'label' => 'Export Excel Vendor'],
                ['name' => 'vendor_export_pdf',   'label' => 'Export PDF Vendor'],
                ['name' => 'pengadaan_view',      'label' => 'Lihat Pengadaan'],
                ['name' => 'pengadaan_create',    'label' => 'Tambah Pengadaan'],
                ['name' => 'pengadaan_update',    'label' => 'Edit Pengadaan'],
                ['name' => 'pengadaan_delete',    'label' => 'Hapus Pengadaan'],
                ['name' => 'pengadaan_approve',   'label' => 'Approve Pengadaan'],
                ['name' => 'pengadaan_export_excel', 'label' => 'Export Excel Pengadaan'],
                ['name' => 'pengadaan_export_pdf',   'label' => 'Export PDF Pengadaan'],
                ['name' => 'pembayaran_approve',  'label' => 'Approve Pembayaran Invoice'],
            ],
            'Konfigurasi System' => [
                ['name' => 'configuration_view',         'label' => 'Lihat Konfigurasi'],
                ['name' => 'configuration_update',       'label' => 'Edit Konfigurasi'],
                ['name' => 'configuration_export_excel', 'label' => 'Export Excel Konfigurasi'],
                ['name' => 'configuration_export_pdf',   'label' => 'Export PDF Konfigurasi'],
            ],
            'Manajemen User' => [
                ['name' => 'users_view',         'label' => 'Lihat User'],
                ['name' => 'users_create',       'label' => 'Tambah User'],
                ['name' => 'users_update',       'label' => 'Edit User'],
                ['name' => 'users_delete',       'label' => 'Hapus User'],
                ['name' => 'users_approve',      'label' => 'Approve Pendaftaran User'],
                ['name' => 'users_export_excel', 'label' => 'Export Excel User'],
                ['name' => 'users_export_pdf',   'label' => 'Export PDF User'],
                ['name' => 'users_impersonate',  'label' => 'Impersonate User'],
            ],
            'Roles & Permissions' => [
                ['name' => 'roles_view',         'label' => 'Lihat Roles'],
                ['name' => 'roles_create',       'label' => 'Tambah Role'],
                ['name' => 'roles_update',       'label' => 'Edit Role'],
                ['name' => 'roles_delete',       'label' => 'Hapus Role'],
                ['name' => 'roles_export_excel', 'label' => 'Export Excel Roles'],
                ['name' => 'roles_export_pdf',   'label' => 'Export PDF Roles'],
            ],
            'Notifikasi' => [
                ['name' => 'notifications_view', 'label' => 'Lihat Notifikasi'],
                ['name' => 'notifications_send', 'label' => 'Kirim Notifikasi'],
            ],
            'Chat' => [
                ['name' => 'chat_view',   'label' => 'Lihat Chat'],
                ['name' => 'chat_create', 'label' => 'Buat Chat'],
                ['name' => 'chat_delete', 'label' => 'Hapus Chat'],
            ],
            'Akses Data' => [
                ['name' => 'access_all_cabang', 'label' => 'Akses Semua Cabang (COE/Pusat)'],
            ],
        ];

        $allPermissions = Permission::pluck('name')->toArray();
        $mapped = collect($groupMapping)->flatten(1)->pluck('name')->toArray();
        $unmapped = array_diff($allPermissions, $mapped);

        $groups = $groupMapping;

        if (! empty($unmapped)) {
            $groups['Lainnya'] = array_values(array_map(
                fn ($p) => ['name' => $p, 'label' => ucwords(str_replace('_', ' ', $p))],
                $unmapped
            ));
        }

        return $groups;
    }
}
