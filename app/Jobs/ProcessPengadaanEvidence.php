<?php

namespace App\Jobs;

use App\Models\PengadaanEvidence;
use App\Services\FileStorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPengadaanEvidence implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $evidenceId,
        public string $tempPath,
        public string $originalName,
        public string $pengadaanSlug
    ) {}

    public function handle(FileStorageService $fileStorage): void
    {
        $evidence = PengadaanEvidence::find($this->evidenceId);

        if (! $evidence) {
            Log::warning("ProcessPengadaanEvidence: evidence #{$this->evidenceId} not found.");

            return;
        }

        try {
            $result = $fileStorage->moveFromTemp(
                $this->tempPath,
                $this->originalName,
                'pengadaan-evidence',
                [$this->pengadaanSlug]
            );

            $evidence->update([
                'file_path' => $result['path'],
                'file_name' => $result['name'],
                'file_size' => $result['size'],
                'file_status' => 'completed',
                'file_processed_at' => now(),
                'file_error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error("ProcessPengadaanEvidence failed for evidence #{$this->evidenceId}: {$e->getMessage()}");

            $evidence->update([
                'file_status' => 'failed',
                'file_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $evidence = PengadaanEvidence::find($this->evidenceId);

        if ($evidence) {
            $evidence->update([
                'file_status' => 'failed',
                'file_error' => $exception->getMessage(),
            ]);
        }

        Log::error("ProcessPengadaanEvidence job failed permanently for evidence #{$this->evidenceId}: {$exception->getMessage()}");
    }
}
