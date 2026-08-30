<?php

namespace App\Console\Commands;

use App\Services\AlatReminderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAlatKalibrasiReminders extends Command
{
    protected $signature = 'alat:send-kalibrasi-reminders';

    protected $description = 'Kirim email reminder untuk alat dengan kalibrasi expired atau jatuh tempo (threshold dari konfigurasi sistem).';

    public function handle(AlatReminderService $service): int
    {
        $this->info('Memulai pengiriman reminder kalibrasi alat...');

        try {
            $result = $service->sendReminders();

            // Reminder dinonaktifkan di konfigurasi sistem
            if (! empty($result['skipped'])) {
                $this->warn('Reminder kalibrasi alat dinonaktifkan di Konfigurasi Sistem (alat.reminder.is_active=false). Skip.');
                Log::info('Alat kalibrasi reminders skipped: disabled in system configuration.');

                return self::SUCCESS;
            }

            $thresholdDays = $service->getThresholdDays();
            $this->info("Expired: {$result['expired']} alat");
            $this->info("Jatuh tempo (≤{$thresholdDays} hari): {$result['pending']} alat");
            $this->info("Email queued: {$result['queued']}");

            Log::info('Scheduled alat kalibrasi reminders sent', $result);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Gagal mengirim reminder: '.$e->getMessage());
            Log::error('SendAlatKalibrasiReminders command failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
