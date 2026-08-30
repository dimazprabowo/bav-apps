<?php

namespace App\Services;

use App\Jobs\SendAlatKalibrasiReminder;
use App\Models\Alat;
use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AlatReminderService
{
    /**
     * Get alat with expired kalibrasi (tanggal_kalibrasi_berikutnya < today).
     * Cabang-scoped via AlatService pattern.
     */
    public function getExpiredAlat(): Collection
    {
        $today = now()->format('Y-m-d');

        return $this->baseQuery()
            ->whereHas('kalibrasis', function ($q) use ($today) {
                $q->whereNotNull('tanggal_kalibrasi_berikutnya')
                    ->where('tanggal_kalibrasi_berikutnya', '<', $today);
            })
            ->get();
    }

    /**
     * Get alat with kalibrasi expiring soon (≤ threshold_days from today).
     * Threshold dibaca dari system_config('alat.reminder.threshold_days') — default 30 hari.
     */
    public function getExpiringSoonAlat(): Collection
    {
        $today = now()->format('Y-m-d');
        $thresholdDays = (int) SystemConfiguration::get('alat.reminder.threshold_days', 30);
        $threshold = now()->addDays($thresholdDays)->format('Y-m-d');

        return $this->baseQuery()
            ->whereHas('kalibrasis', function ($q) use ($today, $threshold) {
                $q->whereNotNull('tanggal_kalibrasi_berikutnya')
                    ->where('tanggal_kalibrasi_berikutnya', '>=', $today)
                    ->where('tanggal_kalibrasi_berikutnya', '<=', $threshold);
            })
            ->get();
    }

    /**
     * Cek apakah reminder kalibrasi aktif (system_config 'alat.reminder.is_active').
     */
    public function isReminderActive(): bool
    {
        return (bool) SystemConfiguration::get('alat.reminder.is_active', true);
    }

    /**
     * Get configured threshold days (untuk display di UI).
     */
    public function getThresholdDays(): int
    {
        return (int) SystemConfiguration::get('alat.reminder.threshold_days', 30);
    }

    /**
     * Base query with cabang scoping + eager load latest kalibrasi + cabang.
     */
    protected function baseQuery()
    {
        $query = Alat::with(['cabang', 'kalibrasis' => function ($q) {
            $q->latest('tanggal_kalibrasi')->limit(1);
        }])->active()->approved();

        $canAccessAll = auth()->check() && auth()->user()->can('access_all_cabang');
        if (! $canAccessAll && auth()->check()) {
            $query->where('cabang_id', auth()->user()->cabang_id);
        }

        return $query;
    }

    /**
     * Send reminder emails for expired + expiring-soon alat.
     * Returns summary: ['expired' => N, 'pending' => N, 'recipients' => N].
     *
     * Recipients: users with alat_view permission in the alat's cabang
     * (or all users with access_all_cabang for cross-cabang alat).
     */
    public function sendReminders(): array
    {
        // Guard: jika reminder dinonaktifkan di konfigurasi sistem, skip.
        if (! $this->isReminderActive()) {
            return [
                'expired' => 0,
                'pending' => 0,
                'recipients' => 0,
                'queued' => 0,
                'skipped' => true,
            ];
        }

        $expired = $this->getExpiredAlat();
        $pending = $this->getExpiringSoonAlat();

        $recipients = $this->getRecipients();
        $sentCount = 0;

        DB::transaction(function () use ($expired, $pending, $recipients, &$sentCount) {
            // Expired reminders
            foreach ($expired as $alat) {
                foreach ($recipients as $user) {
                    if ($this->shouldReceive($user, $alat)) {
                        SendAlatKalibrasiReminder::dispatch($alat->id, $user->id, 'expired');
                        $sentCount++;
                    }
                }
            }

            // Pending (expiring soon) reminders
            foreach ($pending as $alat) {
                foreach ($recipients as $user) {
                    if ($this->shouldReceive($user, $alat)) {
                        SendAlatKalibrasiReminder::dispatch($alat->id, $user->id, 'pending');
                        $sentCount++;
                    }
                }
            }

            // Log the bulk send
            activity('alat')
                ->causedBy(auth()->user())
                ->log('Reminder kalibrasi alat dikirim: '.$expired->count().' expired, '.$pending->count().' pending, '.$sentCount.' email queued.');
        });

        return [
            'expired' => $expired->count(),
            'pending' => $pending->count(),
            'recipients' => $recipients->count(),
            'queued' => $sentCount,
        ];
    }

    /**
     * Get users who should receive reminders (active users with alat_view permission).
     */
    protected function getRecipients(): Collection
    {
        return User::active()
            ->whereHas('roles', function ($q) {
                // Users with roles that have alat_view permission
            })
            ->get()
            ->filter(fn ($user) => $user->can('alat_view'));
    }

    /**
     * Check if user should receive reminder for this alat (cabang-scoped).
     */
    protected function shouldReceive(User $user, Alat $alat): bool
    {
        if ($user->can('access_all_cabang')) {
            return true;
        }

        return $user->cabang_id !== null && $user->cabang_id === $alat->cabang_id;
    }
}
