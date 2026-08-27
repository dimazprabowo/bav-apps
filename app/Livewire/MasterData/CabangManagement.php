<?php

namespace App\Livewire\MasterData;

use App\Enums\CabangStatus;
use App\Exports\CabangExport;
use App\Livewire\Traits\HasNotification;
use App\Models\Cabang;
use App\Services\CabangService;
use App\Traits\HasDynamicLike;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class CabangManagement extends Component
{
    use AuthorizesRequests, HasDynamicLike, HasNotification, WithPagination;

    public $search = '';

    public $statusFilter = '';

    public bool $filterChanged = false;

    public $showModal = false;

    public $editMode = false;

    public $cabangId;

    public $code;

    public $name;

    public $address;

    public $phone;

    public $pic_name;

    public $pic_phone;

    public $status = 'active';

    public $showDeleteModal = false;

    public $deletingCabangId;

    public $deletingCabangName;

    public function mount()
    {
        $this->authorize('viewAny', Cabang::class);
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string', 'max:50', $this->editMode ? 'unique:cabangs,code,'.$this->cabangId : 'unique:cabangs,code'],
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
            'status' => ['required', 'string', 'in:'.implode(',', CabangStatus::values())],
        ];
    }

    public function validationAttributes()
    {
        return [
            'code' => 'kode cabang',
            'name' => 'nama cabang',
            'address' => 'alamat',
            'phone' => 'telepon',
            'pic_name' => 'nama PIC',
            'pic_phone' => 'telepon PIC',
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
        return collect(CabangStatus::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function create()
    {
        $this->authorize('create', Cabang::class);
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $cabang = Cabang::findOrFail($id);
        $this->authorize('update', $cabang);

        $this->cabangId = $cabang->id;
        $this->code = $cabang->code;
        $this->name = $cabang->name;
        $this->address = $cabang->address;
        $this->phone = $cabang->phone;
        $this->pic_name = $cabang->pic_name;
        $this->pic_phone = $cabang->pic_phone;
        $this->status = $cabang->status->value;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(CabangService $service)
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
                'address' => $this->address,
                'phone' => $this->phone,
                'pic_name' => $this->pic_name,
                'pic_phone' => $this->pic_phone,
                'status' => $this->status,
            ];

            if ($this->editMode) {
                $cabang = Cabang::findOrFail($this->cabangId);
                $this->authorize('update', $cabang);
                $service->update($cabang, $data);
                $message = 'Cabang berhasil diupdate!';
            } else {
                $this->authorize('create', Cabang::class);
                $service->create($data);
                $message = 'Cabang berhasil ditambahkan!';
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
        $cabang = Cabang::findOrFail($id);
        $this->deletingCabangId = $cabang->id;
        $this->deletingCabangName = $cabang->name;
        $this->showDeleteModal = true;
    }

    public function delete(CabangService $service)
    {
        try {
            $cabang = Cabang::findOrFail($this->deletingCabangId);
            $this->authorize('delete', $cabang);

            if ($cabang->alats()->exists()) {
                $this->notifyError('Cabang tidak dapat dihapus karena masih memiliki alat terkait.');
                $this->showDeleteModal = false;

                return;
            }

            $service->delete($cabang);
            $this->notifySuccess('Cabang berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus cabang ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function toggleStatus($id, CabangService $service)
    {
        try {
            $cabang = Cabang::findOrFail($id);
            $this->authorize('toggleStatus', $cabang);

            $service->toggleStatus($cabang);
            $status = $cabang->fresh()->status->label();
            $this->notifySuccess("Status cabang berhasil diubah menjadi {$status}!");
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mengubah status cabang.');
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
            'cabangId', 'code', 'name', 'address', 'phone',
            'pic_name', 'pic_phone', 'status',
        ]);
        $this->status = CabangStatus::Active->value;
    }

    public function exportExcel()
    {
        $this->authorize('exportExcel', Cabang::class);

        return (new CabangExport($this->search, $this->statusFilter))
            ->download('cabang-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function exportPdf()
    {
        $this->authorize('exportPdf', Cabang::class);

        $operator = $this->getLikeOperator();
        $cabangs = Cabang::withCount('alats')
            ->when($this->search, function ($q) use ($operator) {
                $q->where(function ($q) use ($operator) {
                    $q->where('code', $operator, "%{$this->search}%")
                        ->orWhere('name', $operator, "%{$this->search}%")
                        ->orWhere('pic_name', $operator, "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== null && $this->statusFilter !== '', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('exports.cabang-pdf', ['cabangs' => $cabangs]);
        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'cabang-'.now()->format('Y-m-d-His').'.pdf'
        );
    }

    public function render(CabangService $service)
    {
        $cabangs = $service->getFiltered($this->search, $this->statusFilter);

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$cabangs->total()} data cabang.");
            $this->filterChanged = false;
        }

        return view('livewire.master-data.cabang-management', [
            'cabangs' => $cabangs,
        ]);
    }
}
