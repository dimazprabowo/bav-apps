<?php

namespace App\Livewire\MasterData;

use App\Enums\KategoriItemStatus;
use App\Exports\KategoriItemExport;
use App\Livewire\Traits\HasNotification;
use App\Models\KategoriItem;
use App\Services\KategoriItemService;
use App\Traits\HasDynamicLike;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class KategoriItemManagement extends Component
{
    use AuthorizesRequests, HasDynamicLike, HasNotification, WithPagination;

    public $search = '';

    public $statusFilter = '';

    public bool $filterChanged = false;

    public $showModal = false;

    public $editMode = false;

    public $kategoriItemId;

    public $code;

    public $name;

    public $description;

    public $status = 'aktif';

    public $showDeleteModal = false;

    public $deletingKategoriItemId;

    public $deletingKategoriItemName;

    public function mount()
    {
        $this->authorize('viewAny', KategoriItem::class);
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string', 'max:50', $this->editMode ? 'unique:kategori_items,code,'.$this->kategoriItemId : 'unique:kategori_items,code'],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => ['required', 'string', 'in:'.implode(',', KategoriItemStatus::values())],
        ];
    }

    public function validationAttributes()
    {
        return [
            'code' => 'kode kategori item',
            'name' => 'nama kategori item',
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
        return collect(KategoriItemStatus::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function create()
    {
        $this->authorize('create', KategoriItem::class);
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $kategoriItem = KategoriItem::findOrFail($id);
        $this->authorize('update', $kategoriItem);

        $this->kategoriItemId = $kategoriItem->id;
        $this->code = $kategoriItem->code;
        $this->name = $kategoriItem->name;
        $this->description = $kategoriItem->description;
        $this->status = $kategoriItem->status->value;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(KategoriItemService $service)
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
                $kategoriItem = KategoriItem::findOrFail($this->kategoriItemId);
                $this->authorize('update', $kategoriItem);
                $service->update($kategoriItem, $data);
                $message = 'Kategori item berhasil diupdate!';
            } else {
                $this->authorize('create', KategoriItem::class);
                $service->create($data);
                $message = 'Kategori item berhasil ditambahkan!';
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
        $kategoriItem = KategoriItem::findOrFail($id);
        $this->deletingKategoriItemId = $kategoriItem->id;
        $this->deletingKategoriItemName = $kategoriItem->name;
        $this->showDeleteModal = true;
    }

    public function delete(KategoriItemService $service)
    {
        try {
            $kategoriItem = KategoriItem::findOrFail($this->deletingKategoriItemId);
            $this->authorize('delete', $kategoriItem);

            $service->delete($kategoriItem);
            $this->notifySuccess('Kategori item berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus kategori item ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function toggleStatus($id, KategoriItemService $service)
    {
        try {
            $kategoriItem = KategoriItem::findOrFail($id);
            $this->authorize('toggleStatus', $kategoriItem);

            $service->toggleStatus($kategoriItem);
            $status = $kategoriItem->fresh()->status->label();
            $this->notifySuccess("Status kategori item berhasil diubah menjadi {$status}!");
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mengubah status kategori item.');
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
        $this->reset(['kategoriItemId', 'code', 'name', 'description', 'status']);
        $this->status = KategoriItemStatus::Aktif->value;
    }

    public function exportExcel()
    {
        $this->authorize('exportExcel', KategoriItem::class);

        return (new KategoriItemExport($this->search, $this->statusFilter))
            ->download('kategori-item-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function exportPdf()
    {
        $this->authorize('exportPdf', KategoriItem::class);

        $operator = $this->getLikeOperator();
        $kategoriItems = KategoriItem::withCount(['vendors', 'pengadaanItems'])
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

        $pdf = Pdf::loadView('exports.kategori-item-pdf', ['kategoriItems' => $kategoriItems]);
        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'kategori-item-'.now()->format('Y-m-d-His').'.pdf'
        );
    }

    public function render(KategoriItemService $service)
    {
        $kategoriItems = $service->getFiltered($this->search, $this->statusFilter);

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$kategoriItems->total()} data kategori item.");
            $this->filterChanged = false;
        }

        return view('livewire.master-data.kategori-item-management', [
            'kategoriItems' => $kategoriItems,
        ]);
    }
}
