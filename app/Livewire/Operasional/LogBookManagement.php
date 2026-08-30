<?php

namespace App\Livewire\Operasional;

use App\Enums\AlatKondisi;
use App\Enums\LogBookStatus;
use App\Exports\LogBookExport;
use App\Livewire\Traits\HasNotification;
use App\Models\Cabang;
use App\Models\LogBookPeminjaman;
use App\Services\LogBookPeminjamanService;
use App\Traits\HasDynamicLike;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class LogBookManagement extends Component
{
    use AuthorizesRequests, HasDynamicLike, HasNotification, WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $cabangFilter = '';

    public $alatFilter = '';

    public bool $filterChanged = false;

    // Delete modal
    public $showDeleteModal = false;

    public $deletingLogId;

    // Approve/Reject modals
    public $showApproveModal = false;

    public $showRejectModal = false;

    public $processingLogId;

    public $processingAlatName;

    public $rejectionReason;

    // Return modal
    public $showReturnModal = false;

    public $returningLogId;

    public $returningAlatName;

    public $kondisiKembali = 'baik';

    public $catatanKembali;

    // Cancel modal
    public $showCancelModal = false;

    public $cancellingLogId;

    public $cancellingAlatName;

    public $cancellationReason;

    public function mount()
    {
        $this->authorize('viewAny', LogBookPeminjaman::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingCabangFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingAlatFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function resetFilters()
    {
        $this->reset(['statusFilter', 'cabangFilter', 'alatFilter']);
        $this->resetPage();
        $this->filterChanged = true;
        $this->notifySuccess('Filter berhasil direset.');
    }

    public function getStatusOptionsProperty(): array
    {
        return collect(LogBookStatus::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()])->toArray();
    }

    public function getCabangOptionsProperty(): array
    {
        return Cabang::active()->orderBy('name')->get()->map(fn ($c) => [
            'value' => $c->id,
            'label' => $c->name,
        ])->toArray();
    }

    public function getKondisiOptionsProperty(): array
    {
        return collect(AlatKondisi::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()])->toArray();
    }

    public function create()
    {
        $this->authorize('create', LogBookPeminjaman::class);

        return $this->redirect(route('operasional.logbook.create'), navigate: true);
    }

    public function confirmDelete($id)
    {
        $log = LogBookPeminjaman::findOrFail($id);
        $this->authorize('delete', $log);

        $this->deletingLogId = $log->id;
        $this->showDeleteModal = true;
    }

    public function delete(LogBookPeminjamanService $service)
    {
        try {
            $log = LogBookPeminjaman::findOrFail($this->deletingLogId);
            $this->authorize('delete', $log);

            $service->delete($log);
            $this->notifySuccess('LogBook berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus logbook ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmApprove($id)
    {
        $log = LogBookPeminjaman::findOrFail($id);
        $this->authorize('approve', $log);

        $this->processingLogId = $log->id;
        $this->processingAlatName = $log->alat?->name ?? '-';
        $this->showApproveModal = true;
    }

    public function approve(LogBookPeminjamanService $service)
    {
        try {
            $log = LogBookPeminjaman::findOrFail($this->processingLogId);
            $this->authorize('approve', $log);

            $service->approve($log, auth()->id());
            $this->notifySuccess('Peminjaman disetujui! Alat siap diambil.');
            $this->showApproveModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk menyetujui peminjaman.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmReject($id)
    {
        $log = LogBookPeminjaman::findOrFail($id);
        $this->authorize('approve', $log);

        $this->processingLogId = $log->id;
        $this->processingAlatName = $log->alat?->name ?? '-';
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function reject(LogBookPeminjamanService $service)
    {
        $this->validate(['rejectionReason' => 'required|string|max:1000'], [
            'rejectionReason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        try {
            $log = LogBookPeminjaman::findOrFail($this->processingLogId);
            $this->authorize('approve', $log);

            $service->reject($log, auth()->id(), $this->rejectionReason);
            $this->notifySuccess('Peminjaman telah ditolak.');
            $this->showRejectModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk menolak peminjaman.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmCancel($id)
    {
        $log = LogBookPeminjaman::findOrFail($id);
        $this->authorize('cancel', $log);

        $this->cancellingLogId = $log->id;
        $this->cancellingAlatName = $log->alat?->name ?? '-';
        $this->cancellationReason = '';
        $this->showCancelModal = true;
    }

    public function cancel(LogBookPeminjamanService $service)
    {
        $this->validate(['cancellationReason' => 'required|string|max:1000'], [
            'cancellationReason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        try {
            $log = LogBookPeminjaman::findOrFail($this->cancellingLogId);
            $this->authorize('cancel', $log);

            $service->cancel($log, $this->cancellationReason);
            $this->notifySuccess('Peminjaman telah dibatalkan.');
            $this->showCancelModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk membatalkan peminjaman.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function startBorrowing($id, LogBookPeminjamanService $service)
    {
        try {
            $log = LogBookPeminjaman::findOrFail($id);
            $this->authorize('startBorrowing', $log);

            $service->startBorrowing($log);
            $this->notifySuccess('Alat telah diberikan. Status: Dipinjam.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk aksi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmReturn($id)
    {
        $log = LogBookPeminjaman::findOrFail($id);
        $this->authorize('returnAlat', $log);

        $this->returningLogId = $log->id;
        $this->returningAlatName = $log->alat?->name ?? '-';
        $this->kondisiKembali = $log->kondisi_pinjam->value;
        $this->catatanKembali = '';
        $this->showReturnModal = true;
    }

    public function returnAlat(LogBookPeminjamanService $service)
    {
        $this->validate([
            'kondisiKembali' => ['required', 'string', 'in:'.implode(',', AlatKondisi::values())],
            'catatanKembali' => 'nullable|string|max:1000',
        ], [], [
            'kondisiKembali' => 'kondisi saat kembali',
            'catatanKembali' => 'catatan',
        ]);

        try {
            $log = LogBookPeminjaman::findOrFail($this->returningLogId);
            $this->authorize('returnAlat', $log);

            $service->returnAlat($log, $this->kondisiKembali, $this->catatanKembali);
            $this->notifySuccess('Alat berhasil dikembalikan!');
            $this->showReturnModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk aksi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function exportExcel()
    {
        $this->authorize('exportExcel', LogBookPeminjaman::class);

        return (new LogBookExport($this->search, $this->statusFilter, $this->cabangFilter, $this->alatFilter))
            ->download('logbook-peminjaman-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function exportPdf()
    {
        $this->authorize('exportPdf', LogBookPeminjaman::class);

        $operator = $this->getLikeOperator();
        $logs = LogBookPeminjaman::with(['alat.cabang', 'peminjam', 'cabang', 'approver'])
            ->when($this->search, function ($q) use ($operator) {
                $q->where(function ($q) use ($operator) {
                    $q->whereHas('alat', function ($q) use ($operator) {
                        $q->where('code', $operator, "%{$this->search}%")
                            ->orWhere('name', $operator, "%{$this->search}%");
                    })->orWhereHas('peminjam', function ($q) use ($operator) {
                        $q->where('name', $operator, "%{$this->search}%");
                    });
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->cabangFilter, fn ($q) => $q->where('cabang_id', $this->cabangFilter))
            ->when($this->alatFilter, fn ($q) => $q->where('alat_id', $this->alatFilter))
            ->orderBy('tanggal_pinjam', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.logbook-pdf', ['logs' => $logs]);
        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'logbook-peminjaman-'.now()->format('Y-m-d-His').'.pdf'
        );
    }

    public function render(LogBookPeminjamanService $service)
    {
        $logs = $service->getFiltered($this->search, $this->statusFilter, $this->cabangFilter, $this->alatFilter);

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$logs->total()} data logbook.");
            $this->filterChanged = false;
        }

        return view('livewire.operasional.logbook-management', [
            'logs' => $logs,
        ]);
    }
}
