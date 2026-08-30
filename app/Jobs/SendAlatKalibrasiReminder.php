<?php

namespace App\Jobs;

use App\Models\Alat;
use App\Models\User;
use App\Notifications\AlatKalibrasiReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAlatKalibrasiReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $alatId,
        public int $userId,
        public string $type = 'expired'
    ) {}

    public function handle(): void
    {
        $alat = Alat::with(['cabang', 'kalibrasis' => function ($q) {
            $q->latest('tanggal_kalibrasi')->limit(1);
        }])->find($this->alatId);

        $user = User::find($this->userId);

        if (! $alat || ! $user) {
            Log::warning("SendAlatKalibrasiReminder: alat({$this->alatId}) atau user({$this->userId}) tidak ditemukan.");

            return;
        }

        $user->notify(new AlatKalibrasiReminder($alat, $this->type));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendAlatKalibrasiReminder FAILED: alat={$this->alatId}, user={$this->userId}, type={$this->type}. Error: {$exception->getMessage()}");
    }
}
