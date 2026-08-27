<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $cabangPriok = Cabang::where('code', 'TGP')->first();
        $cabangSurabaya = Cabang::where('code', 'SBY')->first();

        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@app.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'phone' => '021-1234566',
                'position' => 'Super Administrator',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (! $superAdmin->hasRole('super admin')) {
            $superAdmin->assignRole('super admin');
        }

        // Admin Pusat (COE) - akses lintas-cabang, tidak terikat 1 cabang
        $adminPusat = User::firstOrCreate(
            ['email' => 'admin@app.com'],
            [
                'name' => 'Admin Pusat',
                'password' => Hash::make('password'),
                'phone' => '021-1234567',
                'position' => 'COE / Pusat',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (! $adminPusat->hasRole('admin pusat')) {
            $adminPusat->assignRole('admin pusat');
        }

        // Admin Cabang - contoh: Cabang Utama Klas Tanjung Priok
        $adminCabang = User::firstOrCreate(
            ['email' => 'admincabang@app.com'],
            [
                'name' => 'Admin Cabang Tanjung Priok',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangPriok?->id,
                'phone' => '021-1234568',
                'position' => 'Kepala Cabang',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (! $adminCabang->hasRole('admin cabang')) {
            $adminCabang->assignRole('admin cabang');
        }

        // Staff Cabang - contoh: Cabang Surabaya
        $staffCabang = User::firstOrCreate(
            ['email' => 'user@app.com'],
            [
                'name' => 'Staff Cabang Surabaya',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangSurabaya?->id,
                'phone' => '021-1234569',
                'position' => 'Staff',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (! $staffCabang->hasRole('staff cabang')) {
            $staffCabang->assignRole('staff cabang');
        }
    }
}
