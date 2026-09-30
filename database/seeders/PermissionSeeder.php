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
     *   entity : dashboard, cabang, vendor, pengadaan, configuration, users, roles, notifications, chat
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

            // Master Data — Klaster
            'klaster_view',
            'klaster_create',
            'klaster_update',
            'klaster_delete',
            'klaster_export_excel',
            'klaster_export_pdf',

            // Master Data — Satuan
            'satuan_view',
            'satuan_create',
            'satuan_update',
            'satuan_delete',
            'satuan_export_excel',
            'satuan_export_pdf',

            // Kategori Item Management
            'kategori_item_view',
            'kategori_item_create',
            'kategori_item_update',
            'kategori_item_delete',
            'kategori_item_export_excel',
            'kategori_item_export_pdf',

            // Master Data — Vendor
            'vendor_view',
            'vendor_create',
            'vendor_update',
            'vendor_delete',
            'vendor_export_excel',
            'vendor_export_pdf',

            // Pengadaan Aset — Pengadaan (Vendor permissions ada di atas)
            'pengadaan_view',
            'pengadaan_create',
            'pengadaan_update',
            'pengadaan_delete',
            'pengadaan_approve',
            'pengadaan_export_excel',
            'pengadaan_export_pdf',
            'pembayaran_approve',

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
            'users_approve',
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
            // untuk bypass scoping cabang_id pada Pengadaan.
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
