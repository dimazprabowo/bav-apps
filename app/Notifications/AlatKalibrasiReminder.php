<?php

namespace App\Notifications;

use App\Models\Alat;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlatKalibrasiReminder extends Notification
{
    use Queueable;

    /**
     * Reminder type: 'expired' (sudah lewat) atau 'pending' (akan jatuh tempo ≤30 hari).
     */
    public function __construct(
        public Alat $alat,
        public string $type = 'expired'
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $alat = $this->alat;
        $latestKal = $alat->latest_kalibrasi;
        $tanggalExpired = $latestKal?->tanggal_kalibrasi_berikutnya;
        $cabang = $alat->cabang?->name ?? '-';

        $subject = $this->type === 'expired'
            ? '[PENTING] Kalibrasi Alat Expired: '.$alat->code.' - '.$alat->name
            : '[Pengingat] Kalibrasi Alat Jatuh Tempo: '.$alat->code.' - '.$alat->name;

        $intro = $this->type === 'expired'
            ? 'Kalibrasi alat berikut telah EXPIRED (lewat jatuh tempo):'
            : 'Kalibrasi alat berikut akan jatuh tempo dalam ≤30 hari:';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.alat-kalibrasi-reminder', [
                'userName' => $notifiable->name ?? 'Pengguna',
                'alat' => $alat,
                'cabang' => $cabang,
                'tanggalExpired' => $tanggalExpired,
                'type' => $this->type,
                'intro' => $intro,
                'alatUrl' => url(route('master-data.alat.index')),
            ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'alat_id' => $this->alat->id,
            'alat_code' => $this->alat->code,
            'alat_name' => $this->alat->name,
            'type' => $this->type,
        ];
    }
}
