<?php

namespace App\Livewire\MasterData;

use App\Enums\SatuanStatus;
use App\Exports\SatuanExport;
use App\Livewire\Traits\HasNotification;
use App\Models\Satuan;
use App\Services\SatuanService;
use App\Traits\HasDynamicLike;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class SatuanManagement extends Component
{
    use AuthorizesRequests, HasDynamicLike, HasNotification, WithPagination;

    public $search = '';

    public $statusFilter = '';

    public bool $filterChanged = false;

    public $showModal = false;

    public $editMode = false;

    public $satuanId;

    public $code;

    public $name;

    public $description;

    public $status = 'aktif';

    public $showDeleteModal = false;

    public $deletingSatuanId;

    public $deletingSatuanName;

    public function mount()
    {
        $this->authorize('viewAny', Satuan::class);
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string', 'max:50', $this->editMode ? 'unique:satuans,code,'.$this->satuanId : 'unique:satuans,code'],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => ['required', 'string', 'in:'.implode(',', SatuanStatus::values())],
        ];
    }

    public function validationAttributes()
    {
        return [
            'code' => 'kode satuan',
            'name' => 'nama satuan',
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
        return collect(SatuanStatus::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function create()
    {
        $this->authorize('create', Satuan::class);
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $satuan = Satuan::findOrFail($id);
        $this->authorize('update', $satuan);

        $this->satuanId = $satuan->id;
        $this->code = $satuan->code;
        $this->name = $satuan->name;
        $this->description = $satuan->description;
        $this->status = $satuan->status->value;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(SatuanService $service)
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
                $satuan = Satuan::findOrFail($this->satuanId);
                $this->authorize('update', $satuan);
                $service->update($satuan, $data);
                $message = 'Satuan berhasil diupdate!';
            } else {
                $this->authorize('create', Satuan::class);
                $service->create($data);
                $message = 'Satuan berhasil ditambahkan!';
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
        $satuan = Satuan::findOrFail($id);
        $this->deletingSatuanId = $satuan->id;
        $this->deletingSatuanName = $satuan->name;
        $this->showDeleteModal = true;
    }

    public function delete(SatuanService $service)
    {
        try {
            $satuan = Satuan::findOrFail($this->deletingSatuanId);
            $this->authorize('delete', $satuan);

            $service->delete($satuan);
            $this->notifySuccess('Satuan berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus satuan ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function toggleStatus($id, SatuanService $service)
    {
        try {
            $satuan = Satuan::findOrFail($id);
            $this->authorize('toggleStatus', $satuan);

            $service->toggleStatus($satuan);
            $status = $satuan->fresh()->status->label();
            $this->notifySuccess("Status satuan berhasil diubah menjadi {$status}!");
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mengubah status satuan.');
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
            'satuanId', 'code', 'name', 'description', 'status',
        ]);
        $this->status = SatuanStatus::Aktif->value;
    }

    public function exportExcel()
    {
        $this->authorize('exportExcel', Satuan::class);

        return (new SatuanExport($this->search, $this->statusFilter))
            ->download('satuan-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function exportPdf()
    {
        $this->authorize('exportPdf', Satuan::class);

        $operator = $this->getLikeOperator();
        $satuans = Satuan::withCount('pengadaanItems')
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

        $pdf = Pdf::loadView('exports.satuan-pdf', ['satuans' => $satuans]);
        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'satuan-'.now()->format('Y-m-d-His').'.pdf'
        );
    }

    public function render(SatuanService $service)
    {
        $satuans = $service->getFiltered($this->search, $this->statusFilter);

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$satuans->total()} data satuan.");
            $this->filterChanged = false;
        }

        return view('livewire.master-data.satuan-management', [
            'satuans' => $satuans,
        ]);
    }
}
