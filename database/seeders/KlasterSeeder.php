<?php

namespace Database\Seeders;

use App\Enums\KlasterStatus;
use App\Models\Klaster;
use Illuminate\Database\Seeder;

class KlasterSeeder extends Seeder
{
    public function run(): void
    {
        $klasters = [
            [
                'code' => 'KLS-001',
                'name' => 'Alat Ukur & Instrumen',
                'description' => 'Vendor penyedia alat ukur, instrumen, dan perangkat pengujian.',
                'status' => KlasterStatus::Aktif->value,
            ],
            [
                'code' => 'KLS-002',
                'name' => 'Jasa Kalibrasi & Pengujian',
                'description' => 'Vendor penyedia jasa kalibrasi, pengujian, dan sertifikasi alat.',
                'status' => KlasterStatus::Aktif->value,
            ],
            [
                'code' => 'KLS-003',
                'name' => 'Marine Equipment',
                'description' => 'Vendor penyedia peralatan dan perlengkapan kemaritiman.',
                'status' => KlasterStatus::Aktif->value,
            ],
            [
                'code' => 'KLS-004',
                'name' => 'IT & Elektronik',
                'description' => 'Vendor penyedia perangkat IT, elektronik, dan sistem informasi.',
                'status' => KlasterStatus::Aktif->value,
            ],
            [
                'code' => 'KLS-005',
                'name' => 'Spare Part & Consumable',
                'description' => 'Vendor penyedia suku cadang dan barang habis pakai.',
                'status' => KlasterStatus::Aktif->value,
            ],
            [
                'code' => 'KLS-006',
                'name' => 'Safety & K3',
                'description' => 'Vendor penyedia alat pelindung diri dan perlengkapan keselamatan kerja.',
                'status' => KlasterStatus::Aktif->value,
            ],
            [
                'code' => 'KLS-007',
                'name' => 'Logistik & Transportasi',
                'description' => 'Vendor penyedia jasa logistik, pengiriman, dan transportasi.',
                'status' => KlasterStatus::Aktif->value,
            ],
        ];

        foreach ($klasters as $klaster) {
            Klaster::firstOrCreate(
                ['code' => $klaster['code']],
                $klaster
            );
        }
    }
}
