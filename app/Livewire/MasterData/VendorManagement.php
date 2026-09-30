<?php

namespace App\Livewire\MasterData;

use App\Enums\VendorStatus;
use App\Exports\VendorExport;
use App\Livewire\Traits\HasNotification;
use App\Models\Klaster;
use App\Models\Vendor;
use App\Services\VendorService;
use App\Traits\HasDynamicLike;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class VendorManagement extends Component
{
    use AuthorizesRequests, HasDynamicLike, HasNotification, WithPagination;

    public $search = '';

    public $statusFilter = '';

    public bool $filterChanged = false;

    public $showModal = false;

    public $editMode = false;

    public $vendorId;

    public $klaster_id;

    public $code;

    public $name;

    public $contact_person;

    public $phone;

    public $email;

    public $address;

    public $npwp;

    public $status = 'aktif';

    public $showDeleteModal = false;

    public $deletingVendorId;

    public $deletingVendorName;

    public function mount()
    {
        $this->authorize('viewAny', Vendor::class);
    }

    public function rules()
    {
        return [
            'klaster_id' => 'required|exists:klasters,id',
            'code' => ['required', 'string', 'max:50', $this->editMode ? 'unique:vendors,code,'.$this->vendorId : 'unique:vendors,code'],
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'npwp' => 'nullable|string|max:30',
            'status' => ['required', 'string', 'in:'.implode(',', VendorStatus::values())],
        ];
    }

    public function validationAttributes()
    {
        return [
            'klaster_id' => 'klaster',
            'code' => 'kode vendor',
            'name' => 'nama vendor',
            'contact_person' => 'nama kontak',
            'phone' => 'telepon',
            'email' => 'email',
            'address' => 'alamat',
            'npwp' => 'NPWP',
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
        return collect(VendorStatus::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function getKlasterOptionsProperty(): array
    {
        return Klaster::active()->orderBy('name')->get()->map(fn ($k) => [
            'value' => $k->id,
            'label' => $k->name,
        ])->toArray();
    }

    public function create()
    {
        $this->authorize('create', Vendor::class);
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);
        $this->authorize('update', $vendor);

        $this->vendorId = $vendor->id;
        $this->klaster_id = $vendor->klaster_id;
        $this->code = $vendor->code;
        $this->name = $vendor->name;
        $this->contact_person = $vendor->contact_person;
        $this->phone = $vendor->phone;
        $this->email = $vendor->email;
        $this->address = $vendor->address;
        $this->npwp = $vendor->npwp;
        $this->status = $vendor->status->value;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(VendorService $service)
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->notifyValidationError($e);
            throw $e;
        }

        try {
            $data = [
                'klaster_id' => $this->klaster_id,
                'code' => strtoupper($this->code),
                'name' => $this->name,
                'contact_person' => $this->contact_person,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'npwp' => $this->npwp,
                'status' => $this->status,
            ];

            if ($this->editMode) {
                $vendor = Vendor::findOrFail($this->vendorId);
                $this->authorize('update', $vendor);
                $service->update($vendor, $data);
                $message = 'Vendor berhasil diupdate!';
            } else {
                $this->authorize('create', Vendor::class);
                $service->create($data);
                $message = 'Vendor berhasil ditambahkan!';
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
        $vendor = Vendor::findOrFail($id);
        $this->deletingVendorId = $vendor->id;
        $this->deletingVendorName = $vendor->name;
        $this->showDeleteModal = true;
    }

    public function delete(VendorService $service)
    {
        try {
            $vendor = Vendor::findOrFail($this->deletingVendorId);
            $this->authorize('delete', $vendor);

            $service->delete($vendor);
            $this->notifySuccess('Vendor berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus vendor ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function toggleStatus($id, VendorService $service)
    {
        try {
            $vendor = Vendor::findOrFail($id);
            $this->authorize('toggleStatus', $vendor);

            $service->toggleStatus($vendor);
            $status = $vendor->fresh()->status->label();
            $this->notifySuccess("Status vendor berhasil diubah menjadi {$status}!");
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mengubah status vendor.');
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
            'vendorId', 'klaster_id', 'code', 'name', 'contact_person', 'phone',
            'email', 'address', 'npwp', 'status',
        ]);
        $this->status = VendorStatus::Aktif->value;
    }

    public function exportExcel()
    {
        $this->authorize('exportExcel', Vendor::class);

        return (new VendorExport($this->search, $this->statusFilter))
            ->download('vendor-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function exportPdf()
    {
        $this->authorize('exportPdf', Vendor::class);

        $operator = $this->getLikeOperator();
        $vendors = Vendor::with('klaster')
            ->when($this->search, function ($q) use ($operator) {
                $q->where(function ($q) use ($operator) {
                    $q->where('code', $operator, "%{$this->search}%")
                        ->orWhere('name', $operator, "%{$this->search}%")
                        ->orWhere('contact_person', $operator, "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== null && $this->statusFilter !== '', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('exports.vendor-pdf', ['vendors' => $vendors]);
        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'vendor-'.now()->format('Y-m-d-His').'.pdf'
        );
    }

    public function render(VendorService $service)
    {
        $vendors = $service->getFiltered($this->search, $this->statusFilter);

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$vendors->total()} data vendor.");
            $this->filterChanged = false;
        }

        return view('livewire.master-data.vendor-management', [
            'vendors' => $vendors,
        ]);
    }
}
