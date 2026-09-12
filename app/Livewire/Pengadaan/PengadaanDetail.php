<?php

namespace App\Livewire\Pengadaan;

use App\Livewire\Traits\HasNotification;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Pengadaan;
use App\Services\FileStorageService;
use App\Services\InvoiceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class PengadaanDetail extends Component
{
    use AuthorizesRequests, HasNotification, WithFileUploads;

    public Pengadaan $pengadaan;

    // Invoice modal state
    public bool $showInvoiceModal = false;

    public ?int $editingInvoiceId = null;

    public $inv_no_invoice;

    public $inv_tanggal_invoice;

    public $inv_jumlah;

    public $inv_jatuh_tempo;

    public $inv_catatan;

    public $inv_file;

    public bool $showDeleteInvoiceModal = false;

    public ?int $deleteInvoiceId = null;

    // Payment modal state
    public bool $showPaymentModal = false;

    public ?int $currentInvoiceId = null;

    public ?int $editingPaymentId = null;

    public $pay_tanggal_bayar;

    public $pay_jumlah_bayar;

    public $pay_metode_bayar;

    public $pay_catatan;

    public $pay_file;

    public bool $showDeletePaymentModal = false;

    public ?int $deletePaymentId = null;

    // Payment approve/reject modal state
    public bool $showApprovePaymentModal = false;

    public bool $showRejectPaymentModal = false;

    public ?int $reviewingPaymentId = null;

    public $pay_rejection_reason;

    public function mount(Pengadaan $pengadaan): void
    {
        $this->authorize('view', $pengadaan);
        $this->loadPengadaan();
    }

    protected function loadPengadaan(): void
    {
        $this->pengadaan = $this->pengadaan->fresh([
            'vendor', 'cabang', 'approver', 'items', 'evidences',
            'invoices.payments.approver',
        ]);
    }

    public function downloadEvidence($evidenceId, FileStorageService $fileStorage)
    {
        $evidence = $this->pengadaan->evidences()->findOrFail($evidenceId);

        if (! $evidence->file_path || $evidence->file_status !== 'completed') {
            $this->notifyError('File belum tersedia untuk diunduh.');

            return;
        }

        return $fileStorage->download($evidence->file_path, $evidence->file_name);
    }

    public function goBack()
    {
        return $this->redirect(route('pengadaan.index'), navigate: true);
    }

    // ============================================================
    // Invoice CRUD
    // ============================================================

    public function openCreateInvoiceModal(): void
    {
        $this->authorize('update', $this->pengadaan);

        $this->resetInvoiceForm();
        $this->editingInvoiceId = null;
        $this->showInvoiceModal = true;
    }

    public function openEditInvoiceModal(int $invoiceId): void
    {
        $this->authorize('update', $this->pengadaan);

        $invoice = $this->pengadaan->invoices()->findOrFail($invoiceId);

        $this->editingInvoiceId = $invoiceId;
        $this->inv_no_invoice = $invoice->no_invoice;
        $this->inv_tanggal_invoice = $invoice->tanggal_invoice?->format('Y-m-d');
        $this->inv_jumlah = (string) $invoice->jumlah;
        $this->inv_jatuh_tempo = $invoice->jatuh_tempo?->format('Y-m-d');
        $this->inv_catatan = $invoice->catatan;
        $this->inv_file = null;
        $this->showInvoiceModal = true;
    }

    public function closeInvoiceModal(): void
    {
        $this->showInvoiceModal = false;
        $this->resetInvoiceForm();
    }

    public function removeInvoiceFile(): void
    {
        $this->inv_file = null;
        $this->resetErrorBag('inv_file');
    }

    protected function resetInvoiceForm(): void
    {
        $this->inv_no_invoice = null;
        $this->inv_tanggal_invoice = null;
        $this->inv_jumlah = null;
        $this->inv_jatuh_tempo = null;
        $this->inv_catatan = null;
        $this->inv_file = null;
        $this->resetErrorBag();
    }

    public function invoiceRules(): array
    {
        return [
            'inv_no_invoice' => 'required|string|max:255',
            'inv_tanggal_invoice' => 'required|date',
            'inv_jumlah' => 'required|numeric|min:0',
            'inv_jatuh_tempo' => 'nullable|date|after_or_equal:inv_tanggal_invoice',
            'inv_catatan' => 'nullable|string|max:2000',
            'inv_file' => 'nullable|'.file_upload_validation_rule('invoice'),
        ];
    }

    public function saveInvoice(InvoiceService $service): void
    {
        $this->authorize('update', $this->pengadaan);

        $this->validate($this->invoiceRules(), [], [
            'inv_no_invoice' => 'no. invoice',
            'inv_tanggal_invoice' => 'tanggal invoice',
            'inv_jumlah' => 'jumlah',
            'inv_jatuh_tempo' => 'jatuh tempo',
            'inv_catatan' => 'catatan',
            'inv_file' => 'file invoice',
        ]);

        try {
            $data = [
                'no_invoice' => $this->inv_no_invoice,
                'tanggal_invoice' => $this->inv_tanggal_invoice,
                'jumlah' => (float) $this->inv_jumlah,
                'jatuh_tempo' => $this->inv_jatuh_tempo,
                'catatan' => $this->inv_catatan,
            ];

            $filePayload = null;
            if ($this->inv_file) {
                $temp = app(FileStorageService::class)->storeTemp($this->inv_file, 'invoice');
                $filePayload = ['temp_path' => $temp['path'], 'original_name' => $temp['original_name']];
            }

            if ($this->editingInvoiceId) {
                $invoice = $this->pengadaan->invoices()->findOrFail($this->editingInvoiceId);
                $service->updateInvoice($invoice, $data);

                if ($filePayload) {
                    $service->replaceInvoiceFile($invoice, $filePayload, Str::slug($this->pengadaan->no_pengadaan));
                }

                $this->notifySuccess('Invoice berhasil diupdate!');
            } else {
                $service->createInvoice($this->pengadaan, $data, $filePayload);
                $this->notifySuccess('Invoice berhasil ditambahkan!');
            }

            $this->closeInvoiceModal();
            $this->loadPengadaan();
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk melakukan aksi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmDeleteInvoice(int $invoiceId): void
    {
        $this->authorize('update', $this->pengadaan);

        $this->deleteInvoiceId = $invoiceId;
        $this->showDeleteInvoiceModal = true;
    }

    public function cancelDeleteInvoice(): void
    {
        $this->showDeleteInvoiceModal = false;
        $this->deleteInvoiceId = null;
    }

    public function deleteInvoice(InvoiceService $service): void
    {
        $this->authorize('update', $this->pengadaan);

        try {
            $invoice = $this->pengadaan->invoices()->findOrFail($this->deleteInvoiceId);
            $service->deleteInvoice($invoice);

            $this->notifySuccess('Invoice berhasil dihapus!');
            $this->cancelDeleteInvoice();
            $this->loadPengadaan();
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function downloadInvoiceFile(int $invoiceId, FileStorageService $fileStorage)
    {
        $invoice = $this->pengadaan->invoices()->findOrFail($invoiceId);

        if (! $invoice->file_path || $invoice->file_status !== 'completed') {
            $this->notifyError('File invoice belum tersedia untuk diunduh.');

            return;
        }

        return $fileStorage->download($invoice->file_path, $invoice->file_name);
    }

    // ============================================================
    // Payment CRUD
    // ============================================================

    public function openCreatePaymentModal(int $invoiceId): void
    {
        $this->authorize('update', $this->pengadaan);

        $this->resetPaymentForm();
        $this->currentInvoiceId = $invoiceId;
        $this->editingPaymentId = null;
        $this->showPaymentModal = true;
    }

    public function openEditPaymentModal(int $invoiceId, int $paymentId): void
    {
        $this->authorize('update', $this->pengadaan);

        $invoice = $this->pengadaan->invoices()->findOrFail($invoiceId);
        $payment = $invoice->payments()->findOrFail($paymentId);

        $this->currentInvoiceId = $invoiceId;
        $this->editingPaymentId = $paymentId;
        $this->pay_tanggal_bayar = $payment->tanggal_bayar?->format('Y-m-d');
        $this->pay_jumlah_bayar = (string) $payment->jumlah_bayar;
        $this->pay_metode_bayar = $payment->metode_bayar;
        $this->pay_catatan = $payment->catatan;
        $this->pay_file = null;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->resetPaymentForm();
    }

    public function removePaymentFile(): void
    {
        $this->pay_file = null;
        $this->resetErrorBag('pay_file');
    }

    protected function resetPaymentForm(): void
    {
        $this->currentInvoiceId = null;
        $this->pay_tanggal_bayar = null;
        $this->pay_jumlah_bayar = null;
        $this->pay_metode_bayar = null;
        $this->pay_catatan = null;
        $this->pay_file = null;
        $this->resetErrorBag();
    }

    public function paymentRules(): array
    {
        return [
            'pay_tanggal_bayar' => 'required|date',
            'pay_jumlah_bayar' => 'required|numeric|min:0',
            'pay_metode_bayar' => 'nullable|string|max:100',
            'pay_catatan' => 'nullable|string|max:2000',
            'pay_file' => 'nullable|'.file_upload_validation_rule('invoice-payment'),
        ];
    }

    public function savePayment(InvoiceService $service): void
    {
        $this->authorize('update', $this->pengadaan);

        $this->validate($this->paymentRules(), [], [
            'pay_tanggal_bayar' => 'tanggal bayar',
            'pay_jumlah_bayar' => 'jumlah bayar',
            'pay_metode_bayar' => 'metode bayar',
            'pay_catatan' => 'catatan',
            'pay_file' => 'bukti transfer',
        ]);

        try {
            $invoice = $this->pengadaan->invoices()->findOrFail($this->currentInvoiceId);

            $data = [
                'tanggal_bayar' => $this->pay_tanggal_bayar,
                'jumlah_bayar' => (float) $this->pay_jumlah_bayar,
                'metode_bayar' => $this->pay_metode_bayar,
                'catatan' => $this->pay_catatan,
            ];

            $filePayload = null;
            if ($this->pay_file) {
                $temp = app(FileStorageService::class)->storeTemp($this->pay_file, 'invoice-payment');
                $filePayload = ['temp_path' => $temp['path'], 'original_name' => $temp['original_name']];
            }

            if ($this->editingPaymentId) {
                $payment = $invoice->payments()->findOrFail($this->editingPaymentId);
                $service->updatePayment($payment, $data);

                if ($filePayload) {
                    $service->replacePaymentFile($payment, $filePayload, Str::slug($this->pengadaan->no_pengadaan));
                }

                $this->notifySuccess('Pembayaran berhasil diupdate!');
            } else {
                $service->createPayment($invoice, $data, $filePayload);
                $this->notifySuccess('Pembayaran berhasil ditambahkan, menunggu approval.');
            }

            $this->closePaymentModal();
            $this->loadPengadaan();
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk melakukan aksi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmDeletePayment(int $paymentId): void
    {
        $this->authorize('update', $this->pengadaan);

        $this->deletePaymentId = $paymentId;
        $this->showDeletePaymentModal = true;
    }

    public function cancelDeletePayment(): void
    {
        $this->showDeletePaymentModal = false;
        $this->deletePaymentId = null;
    }

    public function deletePayment(InvoiceService $service): void
    {
        $this->authorize('update', $this->pengadaan);

        try {
            $payment = InvoicePayment::whereHas('invoice', fn ($q) => $q->where('pengadaan_id', $this->pengadaan->id))
                ->findOrFail($this->deletePaymentId);
            $service->deletePayment($payment);

            $this->notifySuccess('Pembayaran berhasil dihapus!');
            $this->cancelDeletePayment();
            $this->loadPengadaan();
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function downloadPaymentFile(int $paymentId, FileStorageService $fileStorage)
    {
        $payment = InvoicePayment::whereHas('invoice', fn ($q) => $q->where('pengadaan_id', $this->pengadaan->id))
            ->findOrFail($paymentId);

        if (! $payment->file_path || $payment->file_status !== 'completed') {
            $this->notifyError('Bukti transfer belum tersedia untuk diunduh.');

            return;
        }

        return $fileStorage->download($payment->file_path, $payment->file_name);
    }

    // ============================================================
    // Payment Approve/Reject
    // ============================================================

    public function confirmApprovePayment(int $paymentId): void
    {
        $payment = InvoicePayment::whereHas('invoice', fn ($q) => $q->where('pengadaan_id', $this->pengadaan->id))
            ->findOrFail($paymentId);
        $this->authorize('approvePayment', [$this->pengadaan, $payment]);

        $this->reviewingPaymentId = $paymentId;
        $this->showApprovePaymentModal = true;
    }

    public function approvePayment(InvoiceService $service): void
    {
        try {
            $payment = InvoicePayment::whereHas('invoice', fn ($q) => $q->where('pengadaan_id', $this->pengadaan->id))
                ->findOrFail($this->reviewingPaymentId);
            $this->authorize('approvePayment', [$this->pengadaan, $payment]);

            $service->approvePayment($payment, auth()->id());
            $this->notifySuccess('Pembayaran berhasil disetujui!');
            $this->showApprovePaymentModal = false;
            $this->loadPengadaan();
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk menyetujui pembayaran ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmRejectPayment(int $paymentId): void
    {
        $payment = InvoicePayment::whereHas('invoice', fn ($q) => $q->where('pengadaan_id', $this->pengadaan->id))
            ->findOrFail($paymentId);
        $this->authorize('approvePayment', [$this->pengadaan, $payment]);

        $this->reviewingPaymentId = $paymentId;
        $this->pay_rejection_reason = '';
        $this->showRejectPaymentModal = true;
    }

    public function rejectPayment(InvoiceService $service): void
    {
        $this->validate(['pay_rejection_reason' => 'required|string|max:1000'], [
            'pay_rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        try {
            $payment = InvoicePayment::whereHas('invoice', fn ($q) => $q->where('pengadaan_id', $this->pengadaan->id))
                ->findOrFail($this->reviewingPaymentId);
            $this->authorize('approvePayment', [$this->pengadaan, $payment]);

            $service->rejectPayment($payment, auth()->id(), $this->pay_rejection_reason);
            $this->notifySuccess('Pembayaran telah ditolak.');
            $this->showRejectPaymentModal = false;
            $this->loadPengadaan();
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk menolak pembayaran ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function render()
    {
        return view('livewire.pengadaan.pengadaan-detail');
    }
}
