<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Struktur role aplikasi pengadaan aset (COE vs Cabang):
     *
     * - Super Admin  : bypass semua (Gate::before di AuthServiceProvider), tetap disync semua permission.
     * - Admin Pusat  : COE — akses & kontrol penuh SEMUA cabang (punya `access_all_cabang`).
     * - Admin Cabang : kelola pengadaan CABANGNYA SENDIRI saja (data-scoped di Service/Policy
     *                  berdasarkan `access_all_cabang` TIDAK dimiliki role ini).
     * - Staff Cabang : basic — lihat pengadaan cabangnya.
     *
     * PENTING: seluruh authorization di kode (Policy/Service/Blade) HANYA membaca permission,
     * TIDAK PERNAH membaca nama role. Role di sini murni bundel permission untuk kemudahan assign.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Super Admin - All permissions (bypass via Gate::before di AuthServiceProvider)
        $superAdmin = Role::firstOrCreate(['name' => 'super admin']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Admin Pusat (COE) - Full access + akses lintas-cabang
        $adminPusat = Role::firstOrCreate(['name' => 'admin pusat']);
        $adminPusat->syncPermissions(Permission::all());

        // 3. Admin Cabang - Kelola pengadaan cabang sendiri (TANPA access_all_cabang)
        $adminCabang = Role::firstOrCreate(['name' => 'admin cabang']);
        $adminCabang->syncPermissions([
            'dashboard_view',
            'cabang_view',
            'pengadaan_view',
            'pengadaan_create',
            'pengadaan_update',
            'pengadaan_delete',
            'pengadaan_export_excel',
            'pengadaan_export_pdf',
            'notifications_view',
            'chat_view',
            'chat_create',
            'chat_delete',
        ]);

        // 4. Staff Cabang - Basic: lihat pengadaan cabangnya
        $staffCabang = Role::firstOrCreate(['name' => 'staff cabang']);
        $staffCabang->syncPermissions([
            'dashboard_view',
            'cabang_view',
            'pengadaan_view',
            'notifications_view',
            'chat_view',
            'chat_create',
        ]);
    }
}
