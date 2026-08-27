<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Struktur role aplikasi monitoring & peminjaman alat (COE vs Cabang):
     *
     * - Super Admin  : bypass semua (Gate::before di AuthServiceProvider), tetap disync semua permission.
     * - Admin Pusat  : COE — akses & kontrol penuh SEMUA cabang (punya `access_all_cabang`).
     * - Admin Cabang : kelola alat & logbook peminjaman CABANGNYA SENDIRI saja (data-scoped di
     *                  Service/Policy berdasarkan `access_all_cabang` TIDAK dimiliki role ini).
     *                  Tetap bisa mengajukan peminjaman alat cabang lain (logbook_create tidak discope).
     * - Staff Cabang : basic — lihat alat cabangnya, ajukan peminjaman (termasuk lintas-cabang).
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

        // 3. Admin Cabang - Kelola alat & logbook cabang sendiri (TANPA access_all_cabang)
        $adminCabang = Role::firstOrCreate(['name' => 'admin cabang']);
        $adminCabang->syncPermissions([
            'dashboard_view',
            'cabang_view',
            'alat_view',
            'alat_create',
            'alat_update',
            'alat_delete',
            'alat_export_excel',
            'alat_export_pdf',
            'logbook_view',
            'logbook_create',
            'logbook_update',
            'logbook_delete',
            'logbook_approve',
            'logbook_return',
            'logbook_export_excel',
            'logbook_export_pdf',
            'notifications_view',
            'chat_view',
            'chat_create',
            'chat_delete',
        ]);

        // 4. Staff Cabang - Basic: lihat alat cabangnya, ajukan peminjaman (boleh lintas-cabang)
        $staffCabang = Role::firstOrCreate(['name' => 'staff cabang']);
        $staffCabang->syncPermissions([
            'dashboard_view',
            'cabang_view',
            'alat_view',
            'logbook_view',
            'logbook_create',
            'notifications_view',
            'chat_view',
            'chat_create',
        ]);
    }
}
