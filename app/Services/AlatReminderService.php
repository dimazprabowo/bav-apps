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
     * Get alat with valid kalibrasi (tanggal_kalibrasi_berikutnya > threshold).
     * Hanya jumlah yang dipakai di digest email (tidak perlu list detail).
     */
    public function getTerkalibrasiAlat(): Collection
    {
        $thresholdDays = (int) SystemConfiguration::get('alat.reminder.threshold_days', 30);
        $threshold = now()->addDays($thresholdDays)->format('Y-m-d');

        return $this->baseQuery()
            ->whereHas('kalibrasis', function ($q) use ($threshold) {
                $q->whereNotNull('tanggal_kalibrasi_berikutnya')
                    ->where('tanggal_kalibrasi_berikutnya', '>', $threshold);
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
     * Send digest reminder emails — 1 email per user containing 3 sections:
     *  - Terkalibrasi (jumlah saja)
     *  - Akan kadaluarsa (jumlah + list)
     *  - Sudah kadaluarsa (jumlah + list)
     *
     * Returns summary: ['terkalibrasi' => N, 'pending' => N, 'expired' => N, 'recipients' => N, 'queued' => N].
     */
    public function sendReminders(): array
    {
        // Guard: jika reminder dinonaktifkan di konfigurasi sistem, skip.
        if (! $this->isReminderActive()) {
            return [
                'terkalibrasi' => 0,
                'pending' => 0,
                'expired' => 0,
                'recipients' => 0,
                'queued' => 0,
                'skipped' => true,
            ];
        }

        $expired = $this->getExpiredAlat();
        $pending = $this->getExpiringSoonAlat();
        $terkalibrasi = $this->getTerkalibrasiAlat();

        $recipients = $this->getRecipients();
        $sentCount = 0;

        DB::transaction(function () use ($expired, $pending, $terkalibrasi, $recipients, &$sentCount) {
            // Build per-user payload (cabang-scoped).
            // User dengan access_all_cabang menerima semua alat.
            // User lain hanya menerima alat dari cabang-nya.
            foreach ($recipients as $user) {
                $userExpired = $expired->filter(fn ($alat) => $this->shouldReceive($user, $alat))->values();
                $userPending = $pending->filter(fn ($alat) => $this->shouldReceive($user, $alat))->values();
                $userTerkalibrasi = $terkalibrasi->filter(fn ($alat) => $this->shouldReceive($user, $alat))->values();

                // Skip user tanpa data relevan (tidak ada expired/pending/terkalibrasi).
                if ($userExpired->isEmpty() && $userPending->isEmpty() && $userTerkalibrasi->isEmpty()) {
                    continue;
                }

                $payload = $this->buildDigestPayload($userTerkalibrasi, $userPending, $userExpired);

                SendAlatKalibrasiReminder::dispatch($user->id, $payload);
                $sentCount++;
            }

            // Log the bulk send
            activity('alat')
                ->causedBy(auth()->user())
                ->log('Reminder kalibrasi alat (digest) dikirim: '
                    .$terkalibrasi->count().' terkalibrasi, '
                    .$pending->count().' akan kadaluarsa, '
                    .$expired->count().' sudah kadaluarsa, '
                    .$sentCount.' email queued.');
        });

        return [
            'terkalibrasi' => $terkalibrasi->count(),
            'pending' => $pending->count(),
            'expired' => $expired->count(),
            'recipients' => $recipients->count(),
            'queued' => $sentCount,
        ];
    }

    /**
     * Build digest payload untuk notification & email view.
     * Hanya field relevan yang dipakai di email (cegah serialisasi model berlebih).
     */
    protected function buildDigestPayload(Collection $terkalibrasi, Collection $pending, Collection $expired): array
    {
        $mapAlat = fn (Alat $alat) => [
            'code' => $alat->code,
            'name' => $alat->name,
            'merk_type' => $alat->merk_type,
            'serial_number' => $alat->serial_number,
            'cabang' => $alat->cabang?->name ?? '-',
            'lokasi' => $alat->lokasi ?? '-',
            'tanggal_kalibrasi_berikutnya' => $alat->latest_kalibrasi?->tanggal_kalibrasi_berikutnya?->format('d M Y'),
        ];

        return [
            'threshold_days' => $this->getThresholdDays(),
            'terkalibrasi_count' => $terkalibrasi->count(),
            'pending_count' => $pending->count(),
            'expired_count' => $expired->count(),
            'pending_list' => $pending->map($mapAlat)->values()->all(),
            'expired_list' => $expired->map($mapAlat)->values()->all(),
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
