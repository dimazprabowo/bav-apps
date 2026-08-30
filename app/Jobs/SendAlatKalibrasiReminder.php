<?php

namespace App\Jobs;

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

    /**
     * @param  int  $userId  Target user ID.
     * @param  array  $payload  Digest payload: terkalibrasi_count, pending_count, expired_count, pending_list, expired_list, threshold_days.
     */
    public function __construct(
        public int $userId,
        public array $payload
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            Log::warning("SendAlatKalibrasiReminder: user({$this->userId}) tidak ditemukan.");

            return;
        }

        $user->notify(new AlatKalibrasiReminder($this->payload));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendAlatKalibrasiReminder FAILED: user={$this->userId}. Error: {$exception->getMessage()}");
    }
}
