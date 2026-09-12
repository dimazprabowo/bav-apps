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
                'value' => 'BAV Apps',
                'data_type' => 'string',
                'description' => 'Nama aplikasi',
                'is_editable' => true,
                'is_active' => true,
            ],
            [
                'key' => 'app.logo',
                'category' => 'general',
                'value' => 'images/bki-main.webp',
                'data_type' => 'string',
                'description' => 'Path/URL logo aplikasi (untuk halaman login, sidebar, dsb). Path relatif terhadap folder public/ (mis. "images/bki-main.webp") atau URL absolut. Email client tetap memakai email_logo_url() yang di-hosting eksternal.',
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

            // Pengadaan - reminder invoice jatuh tempo (passive reminder)
            [
                'key' => 'pengadaan.invoice_reminder.is_active',
                'category' => 'notification',
                'value' => '1',
                'data_type' => 'boolean',
                'description' => 'Aktifkan/nonaktifkan pengiriman email reminder invoice jatuh tempo harian. Nonaktifkan untuk menghentikan semua reminder otomatis terkait invoice pengadaan.',
                'is_editable' => true,
                'is_active' => true,
            ],
            [
                'key' => 'pengadaan.invoice_reminder.threshold_days',
                'category' => 'notification',
                'value' => '7',
                'data_type' => 'integer',
                'description' => 'Berapa hari sebelum tanggal jatuh tempo sebuah invoice dianggap "akan jatuh tempo" dan mendapat email reminder warning. Default: 7 hari. Invoice yang sudah lewat jatuh tempo juga mendapat reminder overdue.',
                'is_editable' => true,
                'is_active' => true,
            ],
            [
                'key' => 'pengadaan.invoice_reminder.schedule_time',
                'category' => 'notification',
                'value' => '08:00',
                'data_type' => 'string',
                'description' => 'Jam eksekusi reminder invoice jatuh tempo harian (format 24 jam HH:MM). Contoh: 08:00, 06:30. Perubahan akan efektif setelah cache konfigurasi di-clear.',
                'is_editable' => true,
                'is_active' => true,
            ],

            // Pengadaan - threshold & SLA
            [
                'key' => 'pengadaan.approval_sla_hours',
                'category' => 'sla',
                'value' => '48',
                'data_type' => 'integer',
                'description' => 'Target SLA approval pengadaan dalam jam. Pengadaan yang melebihi SLA ditandai "overdue" di dashboard. Default: 48 jam (2 hari kerja).',
                'is_editable' => true,
                'is_active' => true,
            ],
            [
                'key' => 'pengadaan.payment_approval_sla_hours',
                'category' => 'sla',
                'value' => '24',
                'data_type' => 'integer',
                'description' => 'Target SLA approval pembayaran (InvoicePayment) dalam jam. Pembayaran yang melebihi SLA ditandai "overdue" di dashboard. Default: 24 jam (1 hari kerja).',
                'is_editable' => true,
                'is_active' => true,
            ],
            [
                'key' => 'pengadaan.default_jatuh_tempo_days',
                'category' => 'threshold',
                'value' => '30',
                'data_type' => 'integer',
                'description' => 'Default jumlah hari dari tanggal invoice ke tanggal jatuh tempo. Dipakai sebagai pre-fill saat input invoice baru. Default: 30 hari (Net-30).',
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
