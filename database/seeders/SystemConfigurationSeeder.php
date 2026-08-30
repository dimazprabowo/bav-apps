<?php

namespace Database\Seeders;

use App\Models\SystemConfiguration;
use Illuminate\Database\Seeder;

class SystemConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configurations = [
            // General configurations
            [
                'key' => 'app.name',
                'category' => 'general',
                'value' => 'Boilerplate',
                'data_type' => 'string',
                'description' => 'Nama aplikasi',
                'is_editable' => true,
                'is_active' => true,
            ],
            [
                'key' => 'app.timezone',
                'category' => 'general',
                'value' => 'Asia/Jakarta',
                'data_type' => 'string',
                'description' => 'Timezone aplikasi',
                'is_editable' => true,
                'is_active' => true,
            ],

            // Registration configurations
            [
                'key' => 'registration.deadline',
                'category' => 'general',
                'value' => '',
                'data_type' => 'datetime',
                'description' => 'Batas waktu pendaftaran. Pendaftaran aktif jika: ada nilai, belum lewat deadline, dan status aktif. Kosongkan atau nonaktifkan untuk menutup pendaftaran.',
                'is_editable' => true,
                'is_active' => false,
            ],
            [
                'key' => 'registration.closed_message',
                'category' => 'general',
                'value' => 'Pendaftaran telah ditutup. Silakan hubungi administrator untuk informasi lebih lanjut.',
                'data_type' => 'string',
                'description' => 'Pesan yang ditampilkan ketika pendaftaran sudah ditutup',
                'is_editable' => true,
                'is_active' => true,
            ],

            // Alat kalibrasi reminder configurations
            [
                'key' => 'alat.reminder.is_active',
                'category' => 'notification',
                'value' => '1',
                'data_type' => 'boolean',
                'description' => 'Aktifkan/nonaktifkan pengiriman email reminder kalibrasi alat harian. Nonaktifkan untuk menghentikan semua reminder otomatis.',
                'is_editable' => true,
                'is_active' => true,
            ],
            [
                'key' => 'alat.reminder.threshold_days',
                'category' => 'notification',
                'value' => '30',
                'data_type' => 'integer',
                'description' => 'Berapa hari sebelum tanggal kalibrasi berikutnya sebuah alat dianggap "akan jatuh tempo" dan mendapat email reminder warning. Default: 30 hari.',
                'is_editable' => true,
                'is_active' => true,
            ],
            [
                'key' => 'alat.reminder.schedule_time',
                'category' => 'notification',
                'value' => '08:00',
                'data_type' => 'string',
                'description' => 'Jam eksekusi reminder kalibrasi alat harian (format 24 jam HH:MM). Contoh: 08:00, 06:30. Perubahan akan efektif setelah cache konfigurasi di-clear.',
                'is_editable' => true,
                'is_active' => true,
            ],
        ];

        foreach ($configurations as $config) {
            SystemConfiguration::firstOrCreate(
                ['key' => $config['key']],
                collect($config)->except('key')->toArray()
            );
        }
    }
}
