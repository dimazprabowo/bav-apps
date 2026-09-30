<?php

namespace Database\Seeders;

use App\Enums\KategoriItemStatus;
use App\Models\KategoriItem;
use Illuminate\Database\Seeder;

class KategoriItemSeeder extends Seeder
{
    /**
     * Kategori item pengadaan yang umum dipakai. Dipakai PengadaanSeeder untuk map
     * `kategori` (nama legacy) → `kategori_item_id`, dan VendorSeeder untuk
     * attach vendor ↔ kategori item (pivot `kategori_item_vendor`).
     */
    public function run(): void
    {
        $kategoriItems = [
            ['code' => 'KTG-001', 'name' => 'Alat Ukur Listrik', 'description' => 'Multimeter, clamp meter, insulation tester, dan alat ukur kelistrikan lainnya.'],
            ['code' => 'KTG-002', 'name' => 'Aksesoris', 'description' => 'Aksesoris dan komponen pendukung alat (kabel test, probe, baterai, dll).'],
            ['code' => 'KTG-003', 'name' => 'Alat Ukur Dimensi', 'description' => 'Caliper, micrometer, gauge, dan alat ukur dimensi presisi.'],
            ['code' => 'KTG-004', 'name' => 'Marine Electronics', 'description' => 'GPS, radio VHF, echosounder, dan perangkat elektronik maritim.'],
            ['code' => 'KTG-005', 'name' => 'Alat Ukur Lingkungan', 'description' => 'Thermohygrometer, anemometer, dan alat ukur parameter lingkungan.'],
            ['code' => 'KTG-006', 'name' => 'Power Tools', 'description' => 'Bor, gerinda, dan perkakas listrik lainnya.'],
            ['code' => 'KTG-007', 'name' => 'Alat Ukur Tekanan', 'description' => 'Pressure gauge, transmitter, dan alat ukur tekanan lainnya.'],
            ['code' => 'KTG-008', 'name' => 'Safety Equipment', 'description' => 'Alat pelindung diri (APD) dan perlengkapan keselamatan kerja.'],
            ['code' => 'KTG-009', 'name' => 'Material Handling', 'description' => 'Trolley, hand pallet, dan alat bantu angkut barang.'],
            ['code' => 'KTG-010', 'name' => 'Storage', 'description' => 'Toolbox, lemari penyimpanan, dan rak peralatan.'],
            ['code' => 'KTG-011', 'name' => 'Alat Ukur Elektronik', 'description' => 'Oscilloscope, signal generator, dan alat ukur elektronik lainnya.'],
        ];

        foreach ($kategoriItems as $kategoriItem) {
            KategoriItem::firstOrCreate(
                ['code' => $kategoriItem['code']],
                array_merge($kategoriItem, ['status' => KategoriItemStatus::Aktif->value])
            );
        }
    }
}
