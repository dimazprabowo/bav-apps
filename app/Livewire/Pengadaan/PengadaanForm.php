<?php

namespace App\Livewire\Pengadaan;

use App\Livewire\Traits\HasNotification;
use App\Models\Cabang;
use App\Models\Klaster;
use App\Models\Pengadaan;
use App\Models\Vendor;
use App\Services\FileStorageService;
use App\Services\PengadaanService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithFileUploads;

class PengadaanForm extends Component
{
    use AuthorizesRequests, HasNotification, WithFileUploads;

    public $pengadaan;

    public $pengadaanId;

    public bool $editMode = false;

    // Form fields
    public $no_pengadaan;

    public $klaster_id;

    public $vendor_id;

    public $cabang_id;

    public $tanggal_pengadaan;

    public $catatan;

    // Item repeater
    public $items = [];

    // Evidence repeater
    public $evidences = [];

    public $deletedEvidenceIds = [];

    public function mount($pengadaan = null): void
    {
        if ($pengadaan) {
            $this->pengadaan = $pengadaan;
            $this->editMode = true;
            $this->pengadaanId = $pengadaan->id;
            $this->authorize('update', $pengadaan);

            $this->fill([
                'no_pengadaan' => $pengadaan->no_pengadaan,
                'klaster_id' => $pengadaan->vendor->klaster_id,
                'vendor_id' => $pengadaan->vendor_id,
                'cabang_id' => $pengadaan->cabang_id,
                'tanggal_pengadaan' => $pengadaan->tanggal_pengadaan?->format('Y-m-d'),
                'catatan' => $pengadaan->catatan,
            ]);

            $this->items = $pengadaan->items->map(fn ($item) => [
                'id' => $item->id,
                'nama_item' => $item->nama_item,
                'kategori_item' => $item->kategori_item,
                'qty' => $item->qty,
                'satuan' => $item->satuan,
                'harga_satuan' => number_format((float) $item->harga_satuan, 0, '.', ''),
            ])->toArray();

            $this->evidences = $pengadaan->evidences->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'file_name' => $e->file_name,
                'file_status' => $e->file_status,
                'file_size' => $e->file_size,
                'file_error' => $e->file_error,
            ])->toArray();
        } else {
            $this->authorize('create', Pengadaan::class);
            $this->tanggal_pengadaan = now()->format('Y-m-d');
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function rules()
    {
        return [
            'no_pengadaan' => ['required', 'string', 'max:50', $this->editMode ? 'unique:pengadaans,no_pengadaan,'.$this->pengadaanId : 'unique:pengadaans,no_pengadaan'],
            'klaster_id' => 'required|exists:klasters,id',
            'vendor_id' => 'required|exists:vendors,id',
            'cabang_id' => 'nullable|exists:cabangs,id',
            'tanggal_pengadaan' => 'required|date',
            'catatan' => 'nullable|string|max:2000',
            'items' => ['required', 'array', 'min:1', function ($attribute, $value, $fail) {
                $total = 0;
                foreach ($value as $i => $item) {
                    $subtotal = (int) ($item['qty'] ?? 0) * (float) ($item['harga_satuan'] ?? 0);
                    if ($subtotal > 99999999999999.99) {
                        $fail('Subtotal item #'.($i + 1).' melebihi batas maksimal Rp 99.999.999.999.999,99.');

                        return;
                    }
                    $total += $subtotal;
                }
                if ($total > 99999999999999.99) {
                    $fail('Total biaya melebihi batas maksimal Rp 99.999.999.999.999,99.');
                }
            }],
            'items.*.nama_item' => 'required|string|max:255',
            'items.*.kategori_item' => 'nullable|string|max:255',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.satuan' => 'required|string|max:30',
            'items.*.harga_satuan' => 'required|numeric|min:0|max:99999999999999',
            'evidences.*.name' => 'required|string|max:255',
            'evidences.*.file' => 'nullable|'.file_upload_validation_rule('pengadaan-evidence'),
        ];
    }

    public function messages()
    {
        return [
            'items.*.harga_satuan.max' => 'Harga satuan tidak boleh melebihi Rp 99.999.999.999.999.',
        ];
    }

    public function validationAttributes()
    {
        return [
            'no_pengadaan' => 'nomor pengadaan',
            'klaster_id' => 'klaster vendor',
            'vendor_id' => 'vendor',
            'cabang_id' => 'cabang',
            'tanggal_pengadaan' => 'tanggal pengadaan',
            'catatan' => 'catatan',
            'items.*.nama_item' => 'nama item',
            'items.*.kategori_item' => 'kategori item',
            'items.*.qty' => 'jumlah',
            'items.*.satuan' => 'satuan',
            'items.*.harga_satuan' => 'harga satuan',
            'evidences.*.name' => 'nama evidence',
            'evidences.*.file' => 'file evidence',
        ];
    }

    public function getKlasterOptionsProperty(): array
    {
        return Klaster::active()->orderBy('name')->get()->map(fn ($k) => [
            'value' => $k->id,
            'label' => $k->name,
        ])->toArray();
    }

    public function getVendorOptionsProperty(): array
    {
        if (! $this->klaster_id) {
            return [];
        }

        return Vendor::active()->where('klaster_id', $this->klaster_id)->orderBy('name')->get()->map(fn ($v) => [
            'value' => $v->id,
            'label' => $v->name,
        ])->toArray();
    }

    public function updatedKlasterId(): void
    {
        $this->vendor_id = null;
    }

    public function getCabangOptionsProperty(): array
    {
        return Cabang::active()->orderBy('name')->get()->map(fn ($c) => [
            'value' => $c->id,
            'label' => $c->name,
        ])->toArray();
    }

    public function getTotalBiayaProperty(): float
    {
        return collect($this->items)->sum(fn ($item) => (int) ($item['qty'] ?? 0) * (float) ($item['harga_satuan'] ?? 0));
    }

    // ============================================================
    // Item Repeater
    // ============================================================

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'nama_item' => '',
            'kategori_item' => '',
            'qty' => 1,
            'satuan' => 'unit',
            'harga_satuan' => '',
        ];
    }

    public function removeItem($index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    // ============================================================
    // Evidence Repeater
    // ============================================================

    public function addEvidence(): void
    {
        $this->evidences[] = [
            'id' => null,
            'name' => '',
            'file' => null,
            'file_name' => null,
            'file_status' => null,
            'file_size' => null,
            'file_error' => null,
        ];
    }

    public function removeEvidence($index): void
    {
        if (isset($this->evidences[$index]['id']) && $this->evidences[$index]['id']) {
            $this->deletedEvidenceIds[] = $this->evidences[$index]['id'];
        }

        unset($this->evidences[$index]);
        $this->evidences = array_values($this->evidences);
    }

    public function removeEvidenceFile($index): void
    {
        if (isset($this->evidences[$index]['file'])) {
            $this->evidences[$index]['file'] = null;
        }
    }

    public function downloadEvidenceFile($index, FileStorageService $fileStorage)
    {
        $evidence = $this->evidences[$index] ?? null;

        if (! $evidence || ! isset($evidence['id'])) {
            $this->notifyError('Evidence tidak ditemukan.');

            return;
        }

        $record = $this->pengadaan->evidences()->findOrFail($evidence['id']);

        if (! $record->file_path || $record->file_status !== 'completed') {
            $this->notifyError('File belum tersedia untuk diunduh.');

            return;
        }

        return $fileStorage->download($record->file_path, $record->file_name);
    }

    // ============================================================
    // Save
    // ============================================================

    public function save(PengadaanService $service, FileStorageService $fileStorage)
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->notifyValidationError($e);
            throw $e;
        }

        try {
            $data = [
                'no_pengadaan' => strtoupper($this->no_pengadaan),
                'vendor_id' => $this->vendor_id,
                'cabang_id' => $this->cabang_id,
                'tanggal_pengadaan' => $this->tanggal_pengadaan,
                'catatan' => $this->catatan,
            ];

            $items = collect($this->items)->map(fn ($item) => [
                'nama_item' => $item['nama_item'],
                'kategori_item' => $item['kategori_item'] ?: null,
                'qty' => (int) $item['qty'],
                'satuan' => $item['satuan'],
                'harga_satuan' => (float) $item['harga_satuan'],
            ])->toArray();

            $newEvidences = [];
            foreach ($this->evidences as $evidence) {
                if (isset($evidence['id']) && $evidence['id']) {
                    continue;
                }

                if (! isset($evidence['file']) || ! $evidence['file']) {
                    continue;
                }

                $temp = $fileStorage->storeTemp($evidence['file'], 'pengadaan-evidence');
                $newEvidences[] = [
                    'name' => $evidence['name'],
                    'temp_path' => $temp['path'],
                    'original_name' => $temp['original_name'],
                ];
            }

            if ($this->editMode) {
                $pengadaan = Pengadaan::findOrFail($this->pengadaanId);
                $this->authorize('update', $pengadaan);
                $service->update($pengadaan, $data, $items, $newEvidences, $this->deletedEvidenceIds);
                $this->notifySuccess('Pengadaan berhasil diupdate!');

                return $this->redirect(route('pengadaan.show', $pengadaan), navigate: true);
            } else {
                $this->authorize('create', Pengadaan::class);
                $pengadaan = $service->create($data, $items, $newEvidences);
                $this->notifySuccess('Pengadaan berhasil ditambahkan!');

                return $this->redirect(route('pengadaan.show', $pengadaan), navigate: true);
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk melakukan aksi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function cancel()
    {
        return $this->redirect(route('pengadaan.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.pengadaan.pengadaan-form');
    }
}
