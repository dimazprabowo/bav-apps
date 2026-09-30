<?php

namespace App\Livewire\MasterData;

use App\Enums\KlasterStatus;
use App\Exports\KlasterExport;
use App\Livewire\Traits\HasNotification;
use App\Models\Klaster;
use App\Services\KlasterService;
use App\Traits\HasDynamicLike;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class KlasterManagement extends Component
{
    use AuthorizesRequests, HasDynamicLike, HasNotification, WithPagination;

    public $search = '';

    public $statusFilter = '';

    public bool $filterChanged = false;

    public $showModal = false;

    public $editMode = false;

    public $klasterId;

    public $code;

    public $name;

    public $description;

    public $status = 'aktif';

    public $showDeleteModal = false;

    public $deletingKlasterId;

    public $deletingKlasterName;

    public function mount()
    {
        $this->authorize('viewAny', Klaster::class);
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string', 'max:50', $this->editMode ? 'unique:klasters,code,'.$this->klasterId : 'unique:klasters,code'],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => ['required', 'string', 'in:'.implode(',', KlasterStatus::values())],
        ];
    }

    public function validationAttributes()
    {
        return [
            'code' => 'kode klaster',
            'name' => 'nama klaster',
            'description' => 'deskripsi',
            'status' => 'status',
        ];
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

    public function resetFilters()
    {
        $this->statusFilter = '';
        $this->resetPage();
        $this->filterChanged = true;
        $this->notifySuccess('Filter berhasil direset.');
    }

    public function getStatusOptionsProperty(): array
    {
        return collect(KlasterStatus::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function create()
    {
        $this->authorize('create', Klaster::class);
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $klaster = Klaster::findOrFail($id);
        $this->authorize('update', $klaster);

        $this->klasterId = $klaster->id;
        $this->code = $klaster->code;
        $this->name = $klaster->name;
        $this->description = $klaster->description;
        $this->status = $klaster->status->value;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(KlasterService $service)
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->notifyValidationError($e);
            throw $e;
        }

        try {
            $data = [
                'code' => strtoupper($this->code),
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
            ];

            if ($this->editMode) {
                $klaster = Klaster::findOrFail($this->klasterId);
                $this->authorize('update', $klaster);
                $service->update($klaster, $data);
                $message = 'Klaster berhasil diupdate!';
            } else {
                $this->authorize('create', Klaster::class);
                $service->create($data);
                $message = 'Klaster berhasil ditambahkan!';
            }

            $this->notifySuccess($message);
            $this->closeModal();
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk melakukan aksi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmDelete($id)
    {
        $klaster = Klaster::findOrFail($id);
        $this->deletingKlasterId = $klaster->id;
        $this->deletingKlasterName = $klaster->name;
        $this->showDeleteModal = true;
    }

    public function delete(KlasterService $service)
    {
        try {
            $klaster = Klaster::findOrFail($this->deletingKlasterId);
            $this->authorize('delete', $klaster);

            $service->delete($klaster);
            $this->notifySuccess('Klaster berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus klaster ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function toggleStatus($id, KlasterService $service)
    {
        try {
            $klaster = Klaster::findOrFail($id);
            $this->authorize('toggleStatus', $klaster);

            $service->toggleStatus($klaster);
            $status = $klaster->fresh()->status->label();
            $this->notifySuccess("Status klaster berhasil diubah menjadi {$status}!");
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mengubah status klaster.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm()
    {
        $this->reset([
            'klasterId', 'code', 'name', 'description', 'status',
        ]);
        $this->status = KlasterStatus::Aktif->value;
    }

    public function exportExcel()
    {
        $this->authorize('exportExcel', Klaster::class);

        return (new KlasterExport($this->search, $this->statusFilter))
            ->download('klaster-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function exportPdf()
    {
        $this->authorize('exportPdf', Klaster::class);

        $operator = $this->getLikeOperator();
        $klasters = Klaster::withCount('vendors')
            ->when($this->search, function ($q) use ($operator) {
                $q->where(function ($q) use ($operator) {
                    $q->where('code', $operator, "%{$this->search}%")
                        ->orWhere('name', $operator, "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== null && $this->statusFilter !== '', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('exports.klaster-pdf', ['klasters' => $klasters]);
        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'klaster-'.now()->format('Y-m-d-His').'.pdf'
        );
    }

    public function render(KlasterService $service)
    {
        $klasters = $service->getFiltered($this->search, $this->statusFilter);

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$klasters->total()} data klaster.");
            $this->filterChanged = false;
        }

        return view('livewire.master-data.klaster-management', [
            'klasters' => $klasters,
        ]);
    }
}
