<?php

namespace Database\Seeders;

use App\Enums\CabangStatus;
use App\Models\Cabang;
use Illuminate\Database\Seeder;

class CabangSeeder extends Seeder
{
    public function run(): void
    {
        // Daftar resmi 18 cabang PT BKI (Persero) - sumber: bkinusantara.co.id
        $cabangs = [
            [
                'code' => 'SBY',
                'name' => 'Cabang Utama Klas Surabaya',
                'address' => 'Jl. Kalianget No. 14, Surabaya - 60165',
                'phone' => '031-3295448',
                'pic_name' => 'Kepala Cabang Surabaya',
                'pic_phone' => '081200010001',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'BJM',
                'name' => 'Cabang Madya Klas Banjarmasin',
                'address' => 'Jl. Skip Lama No. 19, Banjarmasin - 70117',
                'phone' => '0511-3358311',
                'pic_name' => 'Kepala Cabang Banjarmasin',
                'pic_phone' => '081200010002',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'PLB',
                'name' => 'Cabang Madya Klas Palembang',
                'address' => 'Jl. Perintis Kemerdekaan 5 Ilir, Palembang - 30115',
                'phone' => '0711-713171',
                'pic_name' => 'Kepala Cabang Palembang',
                'pic_phone' => '081200010003',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'BTM',
                'name' => 'Cabang Utama Klas Batam',
                'address' => 'Graha BKI Jl. Yos Sudarso Kav. 5, Batam - 29451',
                'phone' => '0778-433388',
                'pic_name' => 'Kepala Cabang Batam',
                'pic_phone' => '081200010004',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'TGP',
                'name' => 'Cabang Utama Klas Tanjung Priok',
                'address' => 'Jl. Yos Sudarso 38-39-40, Tanjung Priok, Jakarta - 14320',
                'phone' => '021-43930990',
                'pic_name' => 'Kepala Cabang Tanjung Priok',
                'pic_phone' => '081200010005',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'CRB',
                'name' => 'Cabang Pratama Klas Cirebon',
                'address' => 'Jl. Tuparev KM. 3, Cirebon - 45153',
                'phone' => '0231-205266',
                'pic_name' => 'Kepala Cabang Cirebon',
                'pic_phone' => '081200010006',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'MKS',
                'name' => 'Cabang Pratama Klas Makassar',
                'address' => 'Jl. Sungai Cerekang No. 28, Makassar - 90115',
                'phone' => '0411-311993',
                'pic_name' => 'Kepala Cabang Makassar',
                'pic_phone' => '081200010007',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'BTG',
                'name' => 'Cabang Pratama Klas Bitung',
                'address' => 'Jl. Babe Palar No. 53, Madidir Unet, Bitung - 95516',
                'phone' => '0438-38720',
                'pic_name' => 'Kepala Cabang Bitung',
                'pic_phone' => '081200010008',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'SRG',
                'name' => 'Cabang Pratama Klas Sorong',
                'address' => 'Jl. Jend. Sudirman No. 140, Sorong - 98414',
                'phone' => '0951-323870',
                'pic_name' => 'Kepala Cabang Sorong',
                'pic_phone' => '081200010009',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'AMB',
                'name' => 'Cabang Pratama Klas Ambon',
                'address' => 'Jl. Raya Pelabuhan Komp. Pelabuhan, Ambon - 97126',
                'phone' => '0911-349607',
                'pic_name' => 'Kepala Cabang Ambon',
                'pic_phone' => '081200010010',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'SMD',
                'name' => 'Cabang Utama Klas Samarinda',
                'address' => 'Jl. M.T. Haryono No. 199, Air Putih, Samarinda - 75124',
                'phone' => '0541-4121403',
                'pic_name' => 'Kepala Cabang Samarinda',
                'pic_phone' => '081200010011',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'SGP',
                'name' => 'Cabang Utama Klas Singapore',
                'address' => '456 Alexandra Road #24-03 Fragrance Empire Building, Singapore 119962',
                'phone' => '+65-68324060',
                'pic_name' => 'Kepala Cabang Singapore',
                'pic_phone' => '081200010012',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'BLW',
                'name' => 'Cabang Pratama Klas Belawan',
                'address' => 'Jl. Sulawesi II Belawan, Medan - 20412',
                'phone' => '061-6941025',
                'pic_name' => 'Kepala Cabang Belawan',
                'pic_phone' => '081200010013',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'JBI',
                'name' => 'Cabang Pratama Klas Jambi',
                'address' => 'Jl. Bangau IV No. 14 RT. 16 Thohok, Jambi - 36138',
                'phone' => '0741-32180',
                'pic_name' => 'Kepala Cabang Jambi',
                'pic_phone' => '081200010014',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'PNK',
                'name' => 'Cabang Madya Klas Pontianak',
                'address' => 'Jl. Gusti Hamzah No. 211, Pontianak - 78116',
                'phone' => '0561-739579',
                'pic_name' => 'Kepala Cabang Pontianak',
                'pic_phone' => '081200010015',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'PKB',
                'name' => 'Cabang Madya Klas Pekanbaru',
                'address' => 'Jl. Mustika No. 42 Sumahilang, Pekanbaru - 28111',
                'phone' => '0761-854042',
                'pic_name' => 'Kepala Cabang Pekanbaru',
                'pic_phone' => '081200010016',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'SMG',
                'name' => 'Cabang Pratama Klas Semarang',
                'address' => 'Jl. M. Pardi No. 5 Pelabuhan Tg. Emas, Semarang - 50229',
                'phone' => '024-3567260',
                'pic_name' => 'Kepala Cabang Semarang',
                'pic_phone' => '081200010017',
                'status' => CabangStatus::Active->value,
            ],
            [
                'code' => 'BTN',
                'name' => 'Cabang Utama Klas Banten',
                'address' => 'Jl. Gerem Raya KM. 5 No. 1A Kel. Gerem, Kec. Grogol, Cilegon - Banten 42438',
                'phone' => '0254-572673',
                'pic_name' => 'Kepala Cabang Banten',
                'pic_phone' => '081200010018',
                'status' => CabangStatus::Active->value,
            ],
        ];

        foreach ($cabangs as $cabang) {
            Cabang::firstOrCreate(
                ['code' => $cabang['code']],
                $cabang
            );
        }
    }
}
