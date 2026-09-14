<?php

namespace Database\Seeders;

use App\Enums\UserApprovalStatus;
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

        $seedUsers = [
            [
                'email' => 'superadmin@app.com',
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'phone' => '021-1234566',
                'position' => 'Super Administrator',
                'cabang_id' => null,
                'role' => 'super admin',
            ],
            [
                'email' => 'admin@app.com',
                'name' => 'Admin Pusat',
                'password' => Hash::make('password'),
                'phone' => '021-1234567',
                'position' => 'COE / Pusat',
                'cabang_id' => null,
                'role' => 'admin pusat',
            ],
            [
                'email' => 'admincabang@app.com',
                'name' => 'Admin Cabang Tanjung Priok',
                'password' => Hash::make('password'),
                'phone' => '021-1234568',
                'position' => 'Kepala Cabang',
                'cabang_id' => $cabangPriok?->id,
                'role' => 'admin cabang',
            ],
            [
                'email' => 'user@app.com',
                'name' => 'Staff Cabang Surabaya',
                'password' => Hash::make('password'),
                'phone' => '021-1234569',
                'position' => 'Staff',
                'cabang_id' => $cabangSurabaya?->id,
                'role' => 'staff cabang',
            ],
        ];

        foreach ($seedUsers as $data) {
            $role = $data['role'];
            unset($data['role']);

            $data['is_active'] = true;
            $data['approval_status'] = UserApprovalStatus::Approved;
            $data['email_verified_at'] = now();

            $user = User::firstOrCreate(['email' => $data['email']], $data);
            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        }
    }
}
