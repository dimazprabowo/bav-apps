<?php

namespace App\Livewire\MasterData;

use App\Enums\AlatKondisi;
use App\Enums\AlatReviewStatus;
use App\Enums\AlatStatusKalibrasi;
use App\Enums\AlatStatusKepemilikan;
use App\Exports\AlatExport;
use App\Livewire\Traits\HasNotification;
use App\Models\Alat;
use App\Models\Cabang;
use App\Services\AlatReminderService;
use App\Services\AlatService;
use App\Traits\HasDynamicLike;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class AlatManagement extends Component
{
    use AuthorizesRequests, HasDynamicLike, HasNotification, WithPagination;

    public $search = '';

    public $cabangFilter = '';

    public $kondisiFilter = '';

    public $kalibrasiFilter = '';

    public $kepemilikanFilter = '';

    public $reviewFilter = '';

    public bool $filterChanged = false;

    // Delete modal
    public $showDeleteModal = false;

    public $deletingAlatId;

    public $deletingAlatName;

    // Review modals
    public $showApproveModal = false;

    public $showRejectModal = false;

    public $reviewingAlatId;

    public $reviewingAlatName;

    public $approvalNote;

    public $rejectionReason;

    public function mount()
    {
        $this->authorize('viewAny', Alat::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingCabangFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingKondisiFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingKalibrasiFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingKepemilikanFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingReviewFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function resetFilters()
    {
        $this->reset(['cabangFilter', 'kondisiFilter', 'kalibrasiFilter', 'kepemilikanFilter', 'reviewFilter']);
        $this->resetPage();
        $this->filterChanged = true;
        $this->notifySuccess('Filter berhasil direset.');
    }

    public function getKondisiOptionsProperty(): array
    {
        return collect(AlatKondisi::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()])->toArray();
    }

    public function getKalibrasiOptionsProperty(): array
    {
        return collect(AlatStatusKalibrasi::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()])->toArray();
    }

    public function getKepemilikanOptionsProperty(): array
    {
        return collect(AlatStatusKepemilikan::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()])->toArray();
    }

    public function getReviewOptionsProperty(): array
    {
        return collect(AlatReviewStatus::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()])->toArray();
    }

    public function getCabangOptionsProperty(): array
    {
        return Cabang::active()->orderBy('name')->get()->map(fn ($c) => [
            'value' => $c->id,
            'label' => $c->name,
        ])->toArray();
    }

    public function create()
    {
        $this->authorize('create', Alat::class);

        return $this->redirect(route('master-data.alat.create'), navigate: true);
    }

    public function edit($id)
    {
        $alat = Alat::findOrFail($id);
        $this->authorize('update', $alat);

        return $this->redirect(route('master-data.alat.edit', $alat), navigate: true);
    }

    public function show($id)
    {
        $alat = Alat::findOrFail($id);

        return $this->redirect(route('master-data.alat.show', $alat), navigate: true);
    }

    public function confirmDelete($id)
    {
        $alat = Alat::findOrFail($id);
        $this->deletingAlatId = $alat->id;
        $this->deletingAlatName = $alat->name;
        $this->showDeleteModal = true;
    }

    public function delete(AlatService $service)
    {
        try {
            $alat = Alat::findOrFail($this->deletingAlatId);
            $this->authorize('delete', $alat);

            $service->delete($alat);
            $this->notifySuccess('Alat berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus alat ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function toggleStatus($id, AlatService $service)
    {
        try {
            $alat = Alat::findOrFail($id);
            $this->authorize('toggleStatus', $alat);

            $service->toggleStatus($alat);
            $this->notifySuccess('Status alat berhasil diubah!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mengubah status alat.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmApproveReview($id)
    {
        $alat = Alat::findOrFail($id);
        $this->authorize('review', $alat);

        $this->reviewingAlatId = $alat->id;
        $this->reviewingAlatName = $alat->name;
        $this->approvalNote = '';
        $this->showApproveModal = true;
    }

    public function approveReview(AlatService $service)
    {
        try {
            $alat = Alat::findOrFail($this->reviewingAlatId);
            $this->authorize('review', $alat);

            $service->approveReview($alat, auth()->id(), $this->approvalNote);
            $this->notifySuccess('Alat berhasil disetujui!');
            $this->showApproveModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mereview alat.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmRejectReview($id)
    {
        $alat = Alat::findOrFail($id);
        $this->authorize('review', $alat);

        $this->reviewingAlatId = $alat->id;
        $this->reviewingAlatName = $alat->name;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function rejectReview(AlatService $service)
    {
        $this->validate(['rejectionReason' => 'required|string|max:1000'], [
            'rejectionReason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        try {
            $alat = Alat::findOrFail($this->reviewingAlatId);
            $this->authorize('review', $alat);

            $service->rejectReview($alat, auth()->id(), $this->rejectionReason);
            $this->notifySuccess('Alat telah ditolak.');
            $this->showRejectModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mereview alat.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function exportExcel()
    {
        $this->authorize('exportExcel', Alat::class);

        return (new AlatExport(
            $this->search, $this->cabangFilter, $this->kondisiFilter,
            $this->kalibrasiFilter, $this->kepemilikanFilter, $this->reviewFilter
        ))->download('alat-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function exportPdf()
    {
        $this->authorize('exportPdf', Alat::class);

        $operator = $this->getLikeOperator();
        $alats = Alat::with(['cabang', 'reviewer', 'kalibrasis' => fn ($q) => $q->latest('tanggal_kalibrasi')->limit(1)])
            ->when($this->search, function ($q) use ($operator) {
                $q->where(function ($q) use ($operator) {
                    $q->where('code', $operator, "%{$this->search}%")
                        ->orWhere('name', $operator, "%{$this->search}%")
                        ->orWhere('merk_type', $operator, "%{$this->search}%")
                        ->orWhere('serial_number', $operator, "%{$this->search}%")
                        ->orWhere('kode_inventaris', $operator, "%{$this->search}%")
                        ->orWhere('lokasi', $operator, "%{$this->search}%");
                });
            })
            ->when($this->cabangFilter, fn ($q) => $q->where('cabang_id', $this->cabangFilter))
            ->when($this->kondisiFilter, fn ($q) => $q->where('kondisi', $this->kondisiFilter))
            ->when($this->kepemilikanFilter, fn ($q) => $q->where('status_kepemilikan', $this->kepemilikanFilter))
            ->when($this->reviewFilter, fn ($q) => $q->where('review_status', $this->reviewFilter))
            ->when($this->kalibrasiFilter, function ($q) {
                $today = now()->format('Y-m-d');
                $threshold = now()->addDays(30)->format('Y-m-d');
                match ($this->kalibrasiFilter) {
                    \App\Enums\AlatStatusKalibrasi::Terkalibrasi->value => $q->whereHas('kalibrasis', fn ($k) => $k->whereNotNull('tanggal_kalibrasi_berikutnya')->where('tanggal_kalibrasi_berikutnya', '>', $threshold)),
                    \App\Enums\AlatStatusKalibrasi::Expired->value => $q->whereHas('kalibrasis', fn ($k) => $k->whereNotNull('tanggal_kalibrasi_berikutnya')->where('tanggal_kalibrasi_berikutnya', '<', $today)),
                    \App\Enums\AlatStatusKalibrasi::Pending->value => $q->whereHas('kalibrasis', fn ($k) => $k->whereNotNull('tanggal_kalibrasi_berikutnya')->where('tanggal_kalibrasi_berikutnya', '>=', $today)->where('tanggal_kalibrasi_berikutnya', '<=', $threshold)),
                    \App\Enums\AlatStatusKalibrasi::TidakPerlu->value => $q->whereDoesntHave('kalibrasis', fn ($k) => $k->whereNotNull('tanggal_kalibrasi_berikutnya')),
                    default => null,
                };
            })
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('exports.alat-pdf', ['alats' => $alats]);
        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'alat-'.now()->format('Y-m-d-His').'.pdf'
        );
    }

    public function sendReminders(AlatReminderService $service)
    {
        $this->authorize('sendReminder', Alat::class);

        try {
            $result = $service->sendReminders();

            // Reminder dinonaktifkan di konfigurasi sistem
            if (! empty($result['skipped'])) {
                $this->notifyWarning('Reminder kalibrasi dinonaktifkan di Konfigurasi Sistem. Aktifkan konfigurasi "alat.reminder.is_active" untuk mengirim reminder.');

                return;
            }

            $thresholdDays = $service->getThresholdDays();
            $this->notifySuccess(
                "Reminder kalibrasi dikirim: {$result['expired']} expired, {$result['pending']} jatuh tempo (≤{$thresholdDays} hari), {$result['queued']} email queued."
            );
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mengirim reminder.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function render(AlatService $service)
    {
        $alats = $service->getFiltered(
            $this->search, $this->cabangFilter, $this->kondisiFilter,
            $this->kalibrasiFilter, $this->kepemilikanFilter, $this->reviewFilter
        );

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$alats->total()} data alat.");
            $this->filterChanged = false;
        }

        return view('livewire.master-data.alat-management', [
            'alats' => $alats,
        ]);
    }
}
