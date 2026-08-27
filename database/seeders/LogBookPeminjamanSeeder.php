<?php

namespace Database\Seeders;

use App\Enums\AlatKondisi;
use App\Enums\LogBookStatus;
use App\Models\Alat;
use App\Models\Cabang;
use App\Models\LogBookPeminjaman;
use App\Models\User;
use Illuminate\Database\Seeder;

class LogBookPeminjamanSeeder extends Seeder
{
    public function run(): void
    {
        $alat1 = Alat::where('code', 'ALT-001')->first(); // Multimeter - approved
        $alat2 = Alat::where('code', 'ALT-002')->first(); // Caliper - approved (expired calibration)
        $alat4 = Alat::where('code', 'ALT-004')->first(); // Thermohygrometer - approved
        $alat5 = Alat::where('code', 'ALT-005')->first(); // Oscilloscope - approved

        $priok = Cabang::where('code', 'TGP')->first();
        $surabaya = Cabang::where('code', 'SBY')->first();
        $makassar = Cabang::where('code', 'MKS')->first();
        $belawan = Cabang::where('code', 'BLW')->first();

        $admin = User::where('email', 'admin@app.com')->first();
        $user = User::where('email', 'user@app.com')->first();

        if (! $alat1 || ! $alat2 || ! $alat4 || ! $alat5 || ! $admin || ! $user) {
            return;
        }

        $logs = [
            [
                'alat_id' => $alat1->id,
                'peminjam_id' => $user->id,
                'cabang_id' => $priok->id,
                'tanggal_pinjam' => now()->subDays(10)->format('Y-m-d'),
                'tanggal_kembali_rencana' => now()->subDays(3)->format('Y-m-d'),
                'tanggal_kembali_aktual' => now()->subDays(3)->format('Y-m-d'),
                'status' => LogBookStatus::Returned->value,
                'kondisi_pinjam' => AlatKondisi::Baik->value,
                'kondisi_kembali' => AlatKondisi::Baik->value,
                'catatan' => 'Peminjaman untuk pengukuran panel listrik.',
                'approved_by' => $admin->id,
                'approved_at' => now()->subDays(11),
                'created_by' => $user->id,
            ],
            [
                'alat_id' => $alat2->id,
                'peminjam_id' => $user->id,
                'cabang_id' => $priok->id,
                'tanggal_pinjam' => now()->subDays(2)->format('Y-m-d'),
                'tanggal_kembali_rencana' => now()->addDays(5)->format('Y-m-d'),
                'tanggal_kembali_aktual' => null,
                'status' => LogBookStatus::Borrowed->value,
                'kondisi_pinjam' => AlatKondisi::Baik->value,
                'kondisi_kembali' => null,
                'catatan' => 'Pengukuran komponen mesin.',
                'approved_by' => $admin->id,
                'approved_at' => now()->subDays(3),
                'created_by' => $user->id,
            ],
            [
                'alat_id' => $alat4->id,
                'peminjam_id' => $user->id,
                'cabang_id' => $makassar->id,
                'tanggal_pinjam' => now()->subDays(15)->format('Y-m-d'),
                'tanggal_kembali_rencana' => now()->subDays(8)->format('Y-m-d'),
                'tanggal_kembali_aktual' => null,
                'status' => LogBookStatus::Overdue->value,
                'kondisi_pinjam' => AlatKondisi::Baik->value,
                'kondisi_kembali' => null,
                'catatan' => 'Pengujian ruang cold storage.',
                'approved_by' => $admin->id,
                'approved_at' => now()->subDays(16),
                'created_by' => $user->id,
            ],
            [
                'alat_id' => $alat5->id,
                'peminjam_id' => $user->id,
                'cabang_id' => $belawan->id,
                'tanggal_pinjam' => now()->addDays(2)->format('Y-m-d'),
                'tanggal_kembali_rencana' => now()->addDays(9)->format('Y-m-d'),
                'tanggal_kembali_aktual' => null,
                'status' => LogBookStatus::Requested->value,
                'kondisi_pinjam' => AlatKondisi::Baik->value,
                'kondisi_kembali' => null,
                'catatan' => 'Analisis sinyal elektronik.',
                'approved_by' => null,
                'approved_at' => null,
                'created_by' => $user->id,
            ],
            [
                'alat_id' => $alat1->id, // Multimeter - approved, cabang Tanjung Priok (sudah returned sebelumnya)
                'peminjam_id' => $user->id,
                'cabang_id' => $priok->id,
                'tanggal_pinjam' => now()->addDays(1)->format('Y-m-d'),
                'tanggal_kembali_rencana' => now()->addDays(7)->format('Y-m-d'),
                'tanggal_kembali_aktual' => null,
                'status' => LogBookStatus::Requested->value,
                'kondisi_pinjam' => AlatKondisi::Baik->value,
                'kondisi_kembali' => null,
                'catatan' => 'Pengukuran panel listrik untuk proyek baru.',
                'approved_by' => null,
                'approved_at' => null,
                'created_by' => $user->id,
            ],
        ];

        foreach ($logs as $log) {
            LogBookPeminjaman::firstOrCreate(
                [
                    'alat_id' => $log['alat_id'],
                    'tanggal_pinjam' => $log['tanggal_pinjam'],
                ],
                $log
            );
        }
    }
}
