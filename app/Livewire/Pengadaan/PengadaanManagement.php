<?php

namespace App\Livewire\Pengadaan;

use App\Enums\PengadaanApprovalStatus;
use App\Exports\PengadaanExport;
use App\Livewire\Traits\HasNotification;
use App\Models\Cabang;
use App\Models\Pengadaan;
use App\Models\Vendor;
use App\Services\PengadaanService;
use App\Traits\HasDynamicLike;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class PengadaanManagement extends Component
{
    use AuthorizesRequests, HasDynamicLike, HasNotification, WithPagination;

    public $search = '';

    public $vendorFilter = '';

    public $cabangFilter = '';

    public $statusFilter = '';

    public bool $filterChanged = false;

    // Delete modal
    public $showDeleteModal = false;

    public $deletingPengadaanId;

    public $deletingPengadaanName;

    // Approve/Reject modals
    public $showApproveModal = false;

    public $showRejectModal = false;

    public $reviewingPengadaanId;

    public $reviewingPengadaanName;

    public $rejectionReason;

    public function mount()
    {
        $this->authorize('viewAny', Pengadaan::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingVendorFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingCabangFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function resetFilters()
    {
        $this->reset(['vendorFilter', 'cabangFilter', 'statusFilter']);
        $this->resetPage();
        $this->filterChanged = true;
        $this->notifySuccess('Filter berhasil direset.');
    }

    public function getVendorOptionsProperty(): array
    {
        return Vendor::active()->orderBy('name')->get()->map(fn ($v) => [
            'value' => $v->id,
            'label' => $v->name,
        ])->toArray();
    }

    public function getCabangOptionsProperty(): array
    {
        return Cabang::active()->orderBy('name')->get()->map(fn ($c) => [
            'value' => $c->id,
            'label' => $c->name,
        ])->toArray();
    }

    public function getStatusOptionsProperty(): array
    {
        return collect(PengadaanApprovalStatus::cases())->map(fn ($c) => [
            'value' => $c->value,
            'label' => $c->label(),
        ])->toArray();
    }

    public function create()
    {
        $this->authorize('create', Pengadaan::class);

        return $this->redirect(route('pengadaan.create'), navigate: true);
    }

    public function edit($id)
    {
        $pengadaan = Pengadaan::findOrFail($id);
        $this->authorize('update', $pengadaan);

        return $this->redirect(route('pengadaan.edit', $pengadaan), navigate: true);
    }

    public function show($id)
    {
        $pengadaan = Pengadaan::findOrFail($id);

        return $this->redirect(route('pengadaan.show', $pengadaan), navigate: true);
    }

    public function confirmDelete($id)
    {
        $pengadaan = Pengadaan::findOrFail($id);
        $this->deletingPengadaanId = $pengadaan->id;
        $this->deletingPengadaanName = $pengadaan->no_pengadaan;
        $this->showDeleteModal = true;
    }

    public function delete(PengadaanService $service)
    {
        try {
            $pengadaan = Pengadaan::findOrFail($this->deletingPengadaanId);
            $this->authorize('delete', $pengadaan);

            $service->delete($pengadaan);
            $this->notifySuccess('Pengadaan berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus pengadaan ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmApprove($id)
    {
        $pengadaan = Pengadaan::findOrFail($id);
        $this->authorize('approve', $pengadaan);

        $this->reviewingPengadaanId = $pengadaan->id;
        $this->reviewingPengadaanName = $pengadaan->no_pengadaan;
        $this->showApproveModal = true;
    }

    public function approve(PengadaanService $service)
    {
        try {
            $pengadaan = Pengadaan::findOrFail($this->reviewingPengadaanId);
            $this->authorize('approve', $pengadaan);

            $service->approve($pengadaan, auth()->id());
            $this->notifySuccess('Pengadaan berhasil disetujui!');
            $this->showApproveModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk menyetujui pengadaan.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmReject($id)
    {
        $pengadaan = Pengadaan::findOrFail($id);
        $this->authorize('approve', $pengadaan);

        $this->reviewingPengadaanId = $pengadaan->id;
        $this->reviewingPengadaanName = $pengadaan->no_pengadaan;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function reject(PengadaanService $service)
    {
        $this->validate(['rejectionReason' => 'required|string|max:1000'], [
            'rejectionReason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        try {
            $pengadaan = Pengadaan::findOrFail($this->reviewingPengadaanId);
            $this->authorize('approve', $pengadaan);

            $service->reject($pengadaan, auth()->id(), $this->rejectionReason);
            $this->notifySuccess('Pengadaan telah ditolak.');
            $this->showRejectModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk menolak pengadaan.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function exportExcel()
    {
        $this->authorize('exportExcel', Pengadaan::class);

        return (new PengadaanExport($this->search, $this->vendorFilter, $this->cabangFilter, $this->statusFilter))
            ->download('pengadaan-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function exportPdf()
    {
        $this->authorize('exportPdf', Pengadaan::class);

        $service = app(PengadaanService::class);
        $pengadaans = $service->getFiltered($this->search, $this->vendorFilter, $this->cabangFilter, $this->statusFilter, perPage: 100000)
            ->getCollection()
            ->load('invoices.payments');

        $pdf = Pdf::loadView('exports.pengadaan-pdf', ['pengadaans' => $pengadaans]);
        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'pengadaan-'.now()->format('Y-m-d-His').'.pdf'
        );
    }

    public function render(PengadaanService $service)
    {
        $pengadaans = $service->getFiltered(
            $this->search, $this->vendorFilter, $this->cabangFilter, $this->statusFilter
        );

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$pengadaans->total()} data pengadaan.");
            $this->filterChanged = false;
        }

        return view('livewire.pengadaan.pengadaan-management', [
            'pengadaans' => $pengadaans,
        ]);
    }
}
