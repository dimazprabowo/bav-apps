<?php

namespace App\Jobs;

use App\Models\AlatKalibrasi;
use App\Services\FileStorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAlatKalibrasi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $kalibrasiId,
        public string $tempPath,
        public string $originalName,
        public string $alatSlug
    ) {}

    public function handle(FileStorageService $fileStorage): void
    {
        $kalibrasi = AlatKalibrasi::find($this->kalibrasiId);

        if (! $kalibrasi) {
            Log::warning("ProcessAlatKalibrasi: kalibrasi #{$this->kalibrasiId} not found.");

            return;
        }

        try {
            $result = $fileStorage->moveFromTemp(
                $this->tempPath,
                $this->originalName,
                'alat-kalibrasi',
                [$this->alatSlug]
            );

            $kalibrasi->update([
                'file_path' => $result['path'],
                'file_name' => $result['name'],
                'file_size' => $result['size'],
                'file_status' => 'completed',
                'file_processed_at' => now(),
                'file_error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error("ProcessAlatKalibrasi failed for kalibrasi #{$this->kalibrasiId}: {$e->getMessage()}");

            $kalibrasi->update([
                'file_status' => 'failed',
                'file_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $kalibrasi = AlatKalibrasi::find($this->kalibrasiId);

        if ($kalibrasi) {
            $kalibrasi->update([
                'file_status' => 'failed',
                'file_error' => $exception->getMessage(),
            ]);
        }

        Log::error("ProcessAlatKalibrasi job failed permanently for kalibrasi #{$this->kalibrasiId}: {$exception->getMessage()}");
    }
}
