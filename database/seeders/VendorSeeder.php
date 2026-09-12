<?php

namespace Database\Seeders;

use App\Enums\VendorStatus;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        // 10 vendor dengan kategori bisnis beragam (alat ukur, kalibrasi, marine equipment,
        // consumables, IT, spare part, safety, logistik). Status campuran (1 nonaktif) agar
        // filter status di index maupun dashboard vendor breakdown punya variasi.
        $vendors = [
            [
                'code' => 'VDR-001',
                'name' => 'PT Sumber Alat Teknik',
                'contact_person' => 'Budi Santoso',
                'phone' => '021-5551234',
                'email' => 'sales@sumberalat.co.id',
                'address' => 'Jl. Industri Raya No. 10, Jakarta Utara - 14250',
                'npwp' => '01.234.567.8-901.000',
                'status' => VendorStatus::Aktif->value,
            ],
            [
                'code' => 'VDR-002',
                'name' => 'CV Kalibrasi Mandiri',
                'contact_person' => 'Siti Rahma',
                'phone' => '031-5559876',
                'email' => 'info@kalibrasimandiri.id',
                'address' => 'Jl. Perak Timur No. 5, Surabaya - 60165',
                'npwp' => '02.345.678.9-012.000',
                'status' => VendorStatus::Aktif->value,
            ],
            [
                'code' => 'VDR-003',
                'name' => 'PT Marine Equipment Indonesia',
                'contact_person' => 'Andi Wijaya',
                'phone' => '021-6677889',
                'email' => 'procurement@marineequip.id',
                'address' => 'Jl. Pelabuhan No. 88, Tanjung Priok, Jakarta - 14320',
                'npwp' => '03.456.789.0-123.000',
                'status' => VendorStatus::Aktif->value,
            ],
            [
                'code' => 'VDR-004',
                'name' => 'PT Fluke Calibration Indonesia',
                'contact_person' => 'Dewi Lestari',
                'phone' => '021-7788990',
                'email' => 'service@flukecal.id',
                'address' => 'Graha Fluke Lantai 5, Jl. Sudirman Kav. 25, Jakarta - 12920',
                'npwp' => '04.567.890.1-234.000',
                'status' => VendorStatus::Aktif->value,
            ],
            [
                'code' => 'VDR-005',
                'name' => 'CV Mitroteknik Supply',
                'contact_person' => 'Rudi Hartono',
                'phone' => '024-3551122',
                'email' => 'order@mitroteknik.co.id',
                'address' => 'Jl. Pemuda No. 45, Semarang - 50241',
                'npwp' => '05.678.901.2-345.000',
                'status' => VendorStatus::Aktif->value,
            ],
            [
                'code' => 'VDR-006',
                'name' => 'PT Testo Instruments Indonesia',
                'contact_person' => 'Maya Sari',
                'phone' => '021-5566778',
                'email' => 'support@testo-id.com',
                'address' => 'Menado Building Lantai 7, Jl. Gatot Subroto, Jakarta - 12710',
                'npwp' => '06.789.012.3-456.000',
                'status' => VendorStatus::Aktif->value,
            ],
            [
                'code' => 'VDR-007',
                'name' => 'PT WIKA Instrument Service',
                'contact_person' => 'Hendra Gunawan',
                'phone' => '022-6677880',
                'email' => 'service@wikainstrument.id',
                'address' => 'Jl. Soekarno Hatta No. 445, Bandung - 40233',
                'npwp' => '07.890.123.4-567.000',
                'status' => VendorStatus::Aktif->value,
            ],
            [
                'code' => 'VDR-008',
                'name' => 'CV Safety Pro Indonesia',
                'contact_person' => 'Lina Marlina',
                'phone' => '031-9988776',
                'email' => 'sales@safetypro.id',
                'address' => 'Jl. Raya Gubeng No. 12, Surabaya - 60281',
                'npwp' => '08.901.234.5-678.000',
                'status' => VendorStatus::Aktif->value,
            ],
            [
                'code' => 'VDR-009',
                'name' => 'PT Mitra Logistik Maritim',
                'contact_person' => 'Ferry Kurniawan',
                'phone' => '0511-3344556',
                'email' => 'logistik@mitramaritim.id',
                'address' => 'Jl. Skip Lama No. 22, Banjarmasin - 70117',
                'npwp' => '09.012.345.6-789.000',
                'status' => VendorStatus::Aktif->value,
            ],
            [
                'code' => 'VDR-010',
                'name' => 'PT Mitra Teknik Lama',
                'contact_person' => 'Eko Prasetyo',
                'phone' => '021-4433221',
                'email' => 'info@miteklama.co.id',
                'address' => 'Jl. Mangga Dua No. 99, Jakarta - 14430',
                'npwp' => '10.123.456.7-890.000',
                'status' => VendorStatus::Nonaktif->value,
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(
                ['code' => $vendor['code']],
                $vendor
            );
        }
    }
}
