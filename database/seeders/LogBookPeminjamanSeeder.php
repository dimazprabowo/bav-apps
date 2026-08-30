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

        // Peminjaman untuk 14 cabang lainnya (1 log per cabang, variasi status).
        // Alat diambil dari alat approved di cabang masing-masing (ALT-009 s/d ALT-022).
        $cabangAlatPairs = [
            ['cabang_code' => 'BJM', 'alat_code' => 'ALT-009', 'status' => LogBookStatus::Returned->value, 'days_pinjam' => 20, 'days_rencana' => 13],
            ['cabang_code' => 'PLB', 'alat_code' => 'ALT-010', 'status' => LogBookStatus::Borrowed->value, 'days_pinjam' => 3, 'days_rencana' => 4],
            ['cabang_code' => 'BTM', 'alat_code' => 'ALT-011', 'status' => LogBookStatus::Requested->value, 'days_pinjam' => 3, 'days_rencana' => 10],
            ['cabang_code' => 'CRB', 'alat_code' => 'ALT-012', 'status' => LogBookStatus::Returned->value, 'days_pinjam' => 30, 'days_rencana' => 23],
            ['cabang_code' => 'BTG', 'alat_code' => 'ALT-013', 'status' => LogBookStatus::Borrowed->value, 'days_pinjam' => 5, 'days_rencana' => 2],
            ['cabang_code' => 'SRG', 'alat_code' => 'ALT-014', 'status' => LogBookStatus::Rejected->value, 'days_pinjam' => 1, 'days_rencana' => 8],
            ['cabang_code' => 'AMB', 'alat_code' => 'ALT-015', 'status' => LogBookStatus::Overdue->value, 'days_pinjam' => 12, 'days_rencana' => 5],
            ['cabang_code' => 'SMD', 'alat_code' => 'ALT-016', 'status' => LogBookStatus::Returned->value, 'days_pinjam' => 25, 'days_rencana' => 18],
            ['cabang_code' => 'SGP', 'alat_code' => 'ALT-017', 'status' => LogBookStatus::Borrowed->value, 'days_pinjam' => 4, 'days_rencana' => 3],
            ['cabang_code' => 'JBI', 'alat_code' => 'ALT-018', 'status' => LogBookStatus::Requested->value, 'days_pinjam' => 5, 'days_rencana' => 12],
            ['cabang_code' => 'PNK', 'alat_code' => 'ALT-019', 'status' => LogBookStatus::Returned->value, 'days_pinjam' => 18, 'days_rencana' => 11],
            ['cabang_code' => 'PKB', 'alat_code' => 'ALT-020', 'status' => LogBookStatus::Borrowed->value, 'days_pinjam' => 6, 'days_rencana' => 1],
            ['cabang_code' => 'SMG', 'alat_code' => 'ALT-021', 'status' => LogBookStatus::Returned->value, 'days_pinjam' => 14, 'days_rencana' => 7],
            ['cabang_code' => 'BTN', 'alat_code' => 'ALT-022', 'status' => LogBookStatus::Cancelled->value, 'days_pinjam' => 2, 'days_rencana' => 9],
            // SBY tidak punya alat approved (ALT-003 pending, ALT-008 hilang) — pinjam antar-cabang dari TGP.
            ['cabang_code' => 'SBY', 'alat_code' => 'ALT-001', 'status' => LogBookStatus::Borrowed->value, 'days_pinjam' => 7, 'days_rencana' => 0],
        ];

        foreach ($cabangAlatPairs as $pair) {
            $cabang = Cabang::where('code', $pair['cabang_code'])->first();
            $alat = Alat::where('code', $pair['alat_code'])->first();
            if (! $cabang || ! $alat) {
                continue;
            }

            $status = $pair['status'];
            $isReturned = $status === LogBookStatus::Returned->value;
            $isRejected = $status === LogBookStatus::Rejected->value;
            $isCancelled = $status === LogBookStatus::Cancelled->value;
            $needsApproval = in_array($status, [
                LogBookStatus::Borrowed->value,
                LogBookStatus::Returned->value,
                LogBookStatus::Overdue->value,
            ]);

            $logs[] = [
                'alat_id' => $alat->id,
                'peminjam_id' => $user->id,
                'cabang_id' => $cabang->id,
                'tanggal_pinjam' => now()->subDays($pair['days_pinjam'])->format('Y-m-d'),
                'tanggal_kembali_rencana' => now()->subDays($pair['days_rencana'])->format('Y-m-d'),
                'tanggal_kembali_aktual' => $isReturned ? now()->subDays($pair['days_rencana'])->format('Y-m-d') : null,
                'status' => $status,
                'kondisi_pinjam' => AlatKondisi::Baik->value,
                'kondisi_kembali' => $isReturned ? AlatKondisi::Baik->value : null,
                'catatan' => 'Peminjaman rutin untuk kegiatan operasional cabang.',
                'approved_by' => $needsApproval ? $admin->id : null,
                'approved_at' => $needsApproval ? now()->subDays($pair['days_pinjam'] + 1) : null,
                'rejection_reason' => $isRejected ? 'Alat sedang dalam kondisi rusak.' : null,
                'cancellation_reason' => $isCancelled ? 'Peminjam membatalkan, jadwal berubah.' : null,
                'created_by' => $user->id,
            ];
        }

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
