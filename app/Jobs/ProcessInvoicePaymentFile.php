<?php

namespace App\Jobs;

use App\Models\InvoicePayment;
use App\Services\FileStorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInvoicePaymentFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $paymentId,
        public string $tempPath,
        public string $originalName,
        public string $pengadaanSlug
    ) {}

    public function handle(FileStorageService $fileStorage): void
    {
        $payment = InvoicePayment::find($this->paymentId);

        if (! $payment) {
            Log::warning("ProcessInvoicePaymentFile: payment #{$this->paymentId} not found.");

            return;
        }

        try {
            $result = $fileStorage->moveFromTemp(
                $this->tempPath,
                $this->originalName,
                'pengadaan-aset/payment',
                [$this->pengadaanSlug]
            );

            $payment->update([
                'file_path' => $result['path'],
                'file_name' => $result['name'],
                'file_size' => $result['size'],
                'file_status' => 'completed',
                'file_processed_at' => now(),
                'file_error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error("ProcessInvoicePaymentFile failed for payment #{$this->paymentId}: {$e->getMessage()}");

            $payment->update([
                'file_status' => 'failed',
                'file_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $payment = InvoicePayment::find($this->paymentId);

        if ($payment) {
            $payment->update([
                'file_status' => 'failed',
                'file_error' => $exception->getMessage(),
            ]);
        }

        Log::error("ProcessInvoicePaymentFile job failed permanently for payment #{$this->paymentId}: {$exception->getMessage()}");
    }
}
