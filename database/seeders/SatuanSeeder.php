<?php

namespace Database\Seeders;

use App\Enums\SatuanStatus;
use App\Models\Satuan;
use Illuminate\Database\Seeder;

class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        $satuans = [
            ['code' => 'UNIT', 'name' => 'Unit', 'description' => 'Satuan per unit/buah barang.'],
            ['code' => 'SET', 'name' => 'Set', 'description' => 'Satu set perlengkapan.'],
            ['code' => 'PCS', 'name' => 'Pcs', 'description' => 'Satuan per pieces.'],
            ['code' => 'PACK', 'name' => 'Pack', 'description' => 'Satu paket/kemasan.'],
            ['code' => 'PASANG', 'name' => 'Pasang', 'description' => 'Satuan per pasang (sepasang).'],
            ['code' => 'BOX', 'name' => 'Box', 'description' => 'Satu box/dus.'],
            ['code' => 'METER', 'name' => 'Meter', 'description' => 'Satuan panjang (meter).'],
            ['code' => 'KG', 'name' => 'Kilogram', 'description' => 'Satuan berat (kilogram).'],
            ['code' => 'LITER', 'name' => 'Liter', 'description' => 'Satuan volume (liter).'],
            ['code' => 'JASA', 'name' => 'Jasa', 'description' => 'Pekerjaan/layanan jasa.'],
            ['code' => 'LUMPSUM', 'name' => 'Lumpsum', 'description' => 'Satuan borongan (harga paket total).'],
        ];

        foreach ($satuans as $satuan) {
            Satuan::firstOrCreate(
                ['code' => $satuan['code']],
                [...$satuan, 'status' => SatuanStatus::Aktif->value]
            );
        }
    }
}
