<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\FileStorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInvoiceFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $invoiceId,
        public string $tempPath,
        public string $originalName,
        public string $pengadaanSlug
    ) {}

    public function handle(FileStorageService $fileStorage): void
    {
        $invoice = Invoice::find($this->invoiceId);

        if (! $invoice) {
            Log::warning("ProcessInvoiceFile: invoice #{$this->invoiceId} not found.");

            return;
        }

        try {
            $result = $fileStorage->moveFromTemp(
                $this->tempPath,
                $this->originalName,
                'pengadaan-aset/invoice',
                [$this->pengadaanSlug]
            );

            $invoice->update([
                'file_path' => $result['path'],
                'file_name' => $result['name'],
                'file_size' => $result['size'],
                'file_status' => 'completed',
                'file_processed_at' => now(),
                'file_error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error("ProcessInvoiceFile failed for invoice #{$this->invoiceId}: {$e->getMessage()}");

            $invoice->update([
                'file_status' => 'failed',
                'file_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $invoice = Invoice::find($this->invoiceId);

        if ($invoice) {
            $invoice->update([
                'file_status' => 'failed',
                'file_error' => $exception->getMessage(),
            ]);
        }

        Log::error("ProcessInvoiceFile job failed permanently for invoice #{$this->invoiceId}: {$exception->getMessage()}");
    }
}
