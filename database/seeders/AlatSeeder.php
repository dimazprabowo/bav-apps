<?php

namespace Database\Seeders;

use App\Enums\AlatKondisi;
use App\Enums\AlatReviewStatus;
use App\Enums\AlatStatusKepemilikan;
use App\Enums\KalibrasiHasil;
use App\Models\Alat;
use App\Models\AlatKalibrasi;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Database\Seeder;

class AlatSeeder extends Seeder
{
    public function run(): void
    {
        $priok = Cabang::where('code', 'TGP')->first();
        $surabaya = Cabang::where('code', 'SBY')->first();
        $makassar = Cabang::where('code', 'MKS')->first();
        $belawan = Cabang::where('code', 'BLW')->first();
        $admin = User::where('email', 'admin@app.com')->first();

        if (! $priok || ! $surabaya || ! $makassar || ! $belawan) {
            return;
        }

        $alats = [
            [
                'code' => 'ALT-001',
                'name' => 'Multimeter Digital Fluke 87V',
                'merk_type' => 'Fluke 87V',
                'serial_number' => 'FLK87V-23984756',
                'kode_inventaris' => 'INV-ALT-001',
                'description' => 'Multimeter digital True-RMS untuk pengukuran tegangan, arus, resistansi.',
                'cabang_id' => $priok->id,
                'lokasi' => 'Ruang Lab Kalibrasi 1',
                'kondisi' => AlatKondisi::Baik->value,
                'status_kepemilikan' => AlatStatusKepemilikan::MilikSendiri->value,
                'is_active' => true,
                'review_status' => AlatReviewStatus::Approved->value,
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(30),
                'approval_note' => 'Alat layak digunakan.',
                'kalibrasis' => [
                    [
                        'tanggal_kalibrasi' => '2024-01-15',
                        'tanggal_kalibrasi_berikutnya' => '2025-01-15',
                        'vendor' => 'Kalibrasi Teknik Nusantara',
                        'sertifikat_no' => 'KAL-2024-001',
                        'hasil' => KalibrasiHasil::Lulus->value,
                        'catatan' => 'Hasil kalibrasi baik, alat layak pakai.',
                    ],
                    [
                        'tanggal_kalibrasi' => '2026-01-15',
                        'tanggal_kalibrasi_berikutnya' => '2027-01-15',
                        'vendor' => 'Kalibrasi Teknik Nusantara',
                        'sertifikat_no' => 'KAL-2026-001',
                        'hasil' => KalibrasiHasil::Lulus->value,
                        'catatan' => 'Kalibrasi tahunan, semua parameter dalam toleransi.',
                    ],
                ],
            ],
            [
                'code' => 'ALT-002',
                'name' => 'Caliper Digital Mitutoyo 500-196-30',
                'merk_type' => 'Mitutoyo 500-196-30',
                'serial_number' => 'MTY-500196-98765432',
                'kode_inventaris' => 'INV-ALT-002',
                'description' => 'Jangka sorong digital 0-150mm dengan akurasi 0.01mm.',
                'cabang_id' => $priok->id,
                'lokasi' => 'Ruang Lab Kalibrasi 1',
                'kondisi' => AlatKondisi::Baik->value,
                'status_kepemilikan' => AlatStatusKepemilikan::MilikSendiri->value,
                'is_active' => true,
                'review_status' => AlatReviewStatus::Approved->value,
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(60),
                'approval_note' => 'Approved.',
                'kalibrasis' => [
                    [
                        'tanggal_kalibrasi' => '2024-01-10',
                        'tanggal_kalibrasi_berikutnya' => '2025-01-10',
                        'vendor' => 'PT Metrologi Indonesia',
                        'sertifikat_no' => 'KAL-2024-002',
                        'hasil' => KalibrasiHasil::Lulus->value,
                        'catatan' => 'Akurasi terpenuhi.',
                    ],
                    [
                        'tanggal_kalibrasi' => '2025-01-10',
                        'tanggal_kalibrasi_berikutnya' => '2026-01-10',
                        'vendor' => 'PT Metrologi Indonesia',
                        'sertifikat_no' => 'KAL-2025-002',
                        'hasil' => KalibrasiHasil::Lulus->value,
                        'catatan' => 'Kalibrasi ulang tahunan.',
                    ],
                ],
            ],
            [
                'code' => 'ALT-003',
                'name' => 'Torque Wrench Snap-on QD3R250A',
                'merk_type' => 'Snap-on QD3R250A',
                'serial_number' => 'SNO-QD3R-11223344',
                'kode_inventaris' => 'INV-ALT-003',
                'description' => 'Kunci momen 30-250 Nm dengan akurasi ±4%.',
                'cabang_id' => $surabaya->id,
                'lokasi' => 'Workshop 2',
                'kondisi' => AlatKondisi::RusakRingan->value,
                'status_kepemilikan' => AlatStatusKepemilikan::Sewa->value,
                'is_active' => true,
                'review_status' => AlatReviewStatus::Pending->value,
                'kalibrasis' => [],
            ],
            [
                'code' => 'ALT-004',
                'name' => 'Thermohygrometer Testo 608-H1',
                'merk_type' => 'Testo 608-H1',
                'serial_number' => 'TST-608H1-44556677',
                'kode_inventaris' => 'INV-ALT-004',
                'description' => 'Pengukur suhu & kelembaban ruangan.',
                'cabang_id' => $makassar->id,
                'lokasi' => 'Lab Pengujian 3',
                'kondisi' => AlatKondisi::Baik->value,
                'status_kepemilikan' => AlatStatusKepemilikan::MilikSendiri->value,
                'is_active' => true,
                'review_status' => AlatReviewStatus::Approved->value,
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(15),
                'approval_note' => 'OK.',
                'kalibrasis' => [
                    [
                        'tanggal_kalibrasi' => '2026-02-01',
                        'tanggal_kalibrasi_berikutnya' => now()->addDays(20)->format('Y-m-d'),
                        'vendor' => 'Testo Service Center',
                        'sertifikat_no' => 'KAL-2026-004',
                        'hasil' => KalibrasiHasil::Lulus->value,
                        'catatan' => 'Jatuh tempo dalam 20 hari.',
                    ],
                ],
            ],
            [
                'code' => 'ALT-005',
                'name' => 'Oscilloscope Rigol DS1054Z',
                'merk_type' => 'Rigol DS1054Z',
                'serial_number' => 'RGL-DS1054-88990011',
                'kode_inventaris' => 'INV-ALT-005',
                'description' => 'Oscilloscope digital 4 channel 50 MHz.',
                'cabang_id' => $belawan->id,
                'lokasi' => 'Lab Elektronik',
                'kondisi' => AlatKondisi::Baik->value,
                'status_kepemilikan' => AlatStatusKepemilikan::Leasing->value,
                'is_active' => true,
                'review_status' => AlatReviewStatus::Approved->value,
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(10),
                'approval_note' => 'Approved.',
                'kalibrasis' => [],
            ],
            [
                'code' => 'ALT-006',
                'name' => 'Pressure Gauge WIKA 232.50',
                'merk_type' => 'WIKA 232.50',
                'serial_number' => 'WKA-23250-22334455',
                'kode_inventaris' => 'INV-ALT-006',
                'description' => 'Manometer 0-10 bar dengan diameter 100mm.',
                'cabang_id' => $priok->id,
                'lokasi' => 'Ruang Kalibrasi 2',
                'kondisi' => AlatKondisi::RusakBerat->value,
                'status_kepemilikan' => AlatStatusKepemilikan::MilikSendiri->value,
                'is_active' => false,
                'review_status' => AlatReviewStatus::Rejected->value,
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(5),
                'rejection_reason' => 'Alat rusak berat, perlu penggantian.',
                'kalibrasis' => [
                    [
                        'tanggal_kalibrasi' => '2023-06-01',
                        'tanggal_kalibrasi_berikutnya' => '2024-06-01',
                        'vendor' => 'WIKA Service',
                        'sertifikat_no' => 'KAL-2023-006',
                        'hasil' => KalibrasiHasil::PerluPerbaikan->value,
                        'catatan' => 'Perlu kalibrasi ulang setelah perbaikan.',
                    ],
                    [
                        'tanggal_kalibrasi' => '2024-06-01',
                        'tanggal_kalibrasi_berikutnya' => '2025-06-01',
                        'vendor' => 'WIKA Service',
                        'sertifikat_no' => 'KAL-2024-006',
                        'hasil' => KalibrasiHasil::Gagal->value,
                        'catatan' => 'Alat tidak lulus kalibrasi, rusak berat.',
                    ],
                ],
            ],
            [
                'code' => 'ALT-007',
                'name' => 'Clamp Meter Fluke 376 FC',
                'merk_type' => 'Fluke 376 FC',
                'serial_number' => 'FLK-376FC-66778899',
                'kode_inventaris' => 'INV-ALT-007',
                'description' => 'Clamp meter True-RMS AC/DC dengan Bluetooth untuk logging.',
                'cabang_id' => $belawan->id,
                'lokasi' => 'Lab Listrik Belawan',
                'kondisi' => AlatKondisi::Baik->value,
                'status_kepemilikan' => AlatStatusKepemilikan::MilikSendiri->value,
                'is_active' => true,
                'review_status' => AlatReviewStatus::Approved->value,
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(8),
                'approval_note' => 'Approved.',
                'kalibrasis' => [
                    [
                        'tanggal_kalibrasi' => '2026-01-20',
                        'tanggal_kalibrasi_berikutnya' => '2027-01-20',
                        'vendor' => 'Fluke Calibration Center',
                        'sertifikat_no' => 'KAL-2026-007',
                        'hasil' => KalibrasiHasil::Lulus->value,
                        'catatan' => 'Kalibrasi tahunan, semua parameter OK.',
                    ],
                ],
            ],
            [
                'code' => 'ALT-008',
                'name' => 'Insulation Tester Kyoritsu 3125A',
                'merk_type' => 'Kyoritsu 3125A',
                'serial_number' => 'KYO-3125A-99887766',
                'kode_inventaris' => 'INV-ALT-008',
                'description' => 'Tester isolasi 5000V untuk pengujian tahanan isolasi kabel.',
                'cabang_id' => $surabaya->id,
                'lokasi' => 'Gudang Alat Surabaya',
                'kondisi' => AlatKondisi::Hilang->value,
                'status_kepemilikan' => AlatStatusKepemilikan::MilikSendiri->value,
                'is_active' => false,
                'review_status' => AlatReviewStatus::Approved->value,
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(45),
                'approval_note' => 'Approved sebelum dilaporkan hilang.',
                'kalibrasis' => [],
            ],
        ];

        foreach ($alats as $alatData) {
            $kalibrasisData = $alatData['kalibrasis'] ?? [];
            unset($alatData['kalibrasis']);

            $alat = Alat::firstOrCreate(
                ['code' => $alatData['code']],
                $alatData
            );

            foreach ($kalibrasisData as $kalData) {
                AlatKalibrasi::firstOrCreate(
                    [
                        'alat_id' => $alat->id,
                        'tanggal_kalibrasi' => $kalData['tanggal_kalibrasi'],
                    ],
                    array_merge(['alat_id' => $alat->id], $kalData)
                );
            }
        }
    }
}
