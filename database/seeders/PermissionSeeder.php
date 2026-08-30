<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Single source of truth untuk semua permissions.
     *
     * Idempotent — aman dijalankan berulang kali di production:
     *   php artisan db:seed --class=PermissionSeeder
     *
     * Konvensi penamaan:
     *   {entity}_{action}
     *   entity : dashboard, cabang, alat, logbook, configuration, users, roles, notifications, chat
     *   action : view, create, update, delete, export_excel, export_pdf, send
     *
     * Format ini memudahkan grouping otomatis di UI berdasarkan entity prefix.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard_view',

            // Master Data — Cabang
            'cabang_view',
            'cabang_create',
            'cabang_update',
            'cabang_delete',
            'cabang_export_excel',
            'cabang_export_pdf',

            // Master Data — Alat
            'alat_view',
            'alat_create',
            'alat_update',
            'alat_delete',
            'alat_review',
            'alat_export_excel',
            'alat_export_pdf',
            'alat_send_reminder',

            // Operasional — LogBook Peminjaman
            'logbook_view',
            'logbook_create',
            'logbook_update',
            'logbook_delete',
            'logbook_approve',
            'logbook_return',
            'logbook_cancel',
            'logbook_export_excel',
            'logbook_export_pdf',

            // Konfigurasi System
            'configuration_view',
            'configuration_update',
            'configuration_export_excel',
            'configuration_export_pdf',

            // Manajemen User
            'users_view',
            'users_create',
            'users_update',
            'users_delete',
            'users_export_excel',
            'users_export_pdf',
            'users_impersonate',

            // Roles & Permissions
            'roles_view',
            'roles_create',
            'roles_update',
            'roles_delete',
            'roles_export_excel',
            'roles_export_pdf',

            // Notifikasi
            'notifications_view',
            'notifications_send',

            // Chat / Pesan
            'chat_view',
            'chat_create',
            'chat_delete',

            // Data Scope — akses lintas-cabang (COE/Pusat). Dicek di Service/Policy
            // untuk bypass scoping cabang_id pada Alat & LogBook Peminjaman.
            'access_all_cabang',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
