<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlatKalibrasiReminder extends Notification
{
    use Queueable;

    /**
     * Digest payload:
     *  - threshold_days: int
     *  - terkalibrasi_count: int
     *  - pending_count: int
     *  - expired_count: int
     *  - pending_list: array<array{code,name,merk_type,serial_number,cabang,lokasi,tanggal_kalibrasi_berikutnya}>
     *  - expired_list: array<...>
     *
     * @param  array  $payload  Digest data untuk 1 user.
     */
    public function __construct(
        public array $payload
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $p = $this->payload;
        $expiredCount = (int) ($p['expired_count'] ?? 0);
        $pendingCount = (int) ($p['pending_count'] ?? 0);
        $terkalibrasiCount = (int) ($p['terkalibrasi_count'] ?? 0);
        $thresholdDays = (int) ($p['threshold_days'] ?? 30);

        // Subject dinamis: prioritaskan expired > pending > terkalibrasi.
        $subject = 'Ringkasan Status Kalibrasi Alat';
        if ($expiredCount > 0) {
            $subject = "[PENTING] {$expiredCount} Alat Kalibrasi Sudah Kadaluarsa";
        } elseif ($pendingCount > 0) {
            $subject = "[Pengingat] {$pendingCount} Alat Kalibrasi Akan Kadaluarsa";
        }

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.alat-kalibrasi-reminder', [
                'userName' => $notifiable->name ?? 'Pengguna',
                'thresholdDays' => $thresholdDays,
                'terkalibrasiCount' => $terkalibrasiCount,
                'pendingCount' => $pendingCount,
                'expiredCount' => $expiredCount,
                'pendingList' => $p['pending_list'] ?? [],
                'expiredList' => $p['expired_list'] ?? [],
                'alatUrl' => url(route('master-data.alat.index')),
            ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'terkalibrasi_count' => $this->payload['terkalibrasi_count'] ?? 0,
            'pending_count' => $this->payload['pending_count'] ?? 0,
            'expired_count' => $this->payload['expired_count'] ?? 0,
        ];
    }
}
