<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sapu file upload sementara (temp/*) yang gagal/tidak diproses worker.
Schedule::command('uploads:cleanup-temp')->daily();

// Reminder kalibrasi alat harian (expired + jatuh tempo sesuai threshold dari konfigurasi sistem).
// Jam eksekusi dibaca dari system_config('alat.reminder.schedule_time') — default 08:00.
// Skip otomatis jika alat.reminder.is_active=false (dicek juga di service level).
Schedule::command('alat:send-kalibrasi-reminders')
    ->when(fn () => (bool) \App\Models\SystemConfiguration::get('alat.reminder.is_active', true))
    ->dailyAt(\App\Models\SystemConfiguration::get('alat.reminder.schedule_time', '08:00'));
