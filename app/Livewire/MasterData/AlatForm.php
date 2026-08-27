<?php

namespace App\Livewire\MasterData;

use App\Enums\AlatKondisi;
use App\Enums\AlatStatusKepemilikan;
use App\Livewire\Traits\HasNotification;
use App\Models\Alat;
use App\Models\Cabang;
use App\Services\AlatService;
use App\Services\FileStorageService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithFileUploads;

class AlatForm extends Component
{
    use AuthorizesRequests, HasNotification, WithFileUploads;

    public $alat;

    public $alatId;

    public bool $editMode = false;

    // Form fields
    public $code;

    public $name;

    public $merk_type;

    public $serial_number;

    public $kode_inventaris;

    public $description;

    public $cabang_id;

    public $lokasi;

    public $kondisi = 'baik';

    public $status_kepemilikan = 'milik_sendiri';

    public $is_active = true;

    // Evidence repeater
    public $evidences = [];

    public $deletedEvidenceIds = [];

    public function mount($alat = null): void
    {
        if ($alat) {
            $this->alat = $alat;
            $this->editMode = true;
            $this->alatId = $alat->id;
            $this->authorize('update', $alat);

            $this->fill([
                'code' => $alat->code,
                'name' => $alat->name,
                'merk_type' => $alat->merk_type,
                'serial_number' => $alat->serial_number,
                'kode_inventaris' => $alat->kode_inventaris,
                'description' => $alat->description,
                'cabang_id' => $alat->cabang_id,
                'lokasi' => $alat->lokasi,
                'kondisi' => $alat->kondisi->value,
                'status_kepemilikan' => $alat->status_kepemilikan->value,
                'is_active' => $alat->is_active,
            ]);

            $this->evidences = $alat->evidences->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'file_name' => $e->file_name,
                'file_status' => $e->file_status,
                'file_size' => $e->file_size,
                'file_error' => $e->file_error,
            ])->toArray();
        } else {
            $this->authorize('create', Alat::class);
        }
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string', 'max:50', $this->editMode ? 'unique:alats,code,'.$this->alatId : 'unique:alats,code'],
            'name' => 'required|string|max:255',
            'merk_type' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'kode_inventaris' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'cabang_id' => 'required|exists:cabangs,id',
            'lokasi' => 'nullable|string|max:255',
            'kondisi' => ['required', 'string', 'in:'.implode(',', AlatKondisi::values())],
            'status_kepemilikan' => ['required', 'string', 'in:'.implode(',', AlatStatusKepemilikan::values())],
            'is_active' => 'boolean',
            'evidences.*.name' => 'required|string|max:255',
            'evidences.*.file' => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ];
    }

    public function validationAttributes()
    {
        return [
            'code' => 'kode alat',
            'name' => 'nama alat',
            'merk_type' => 'merk/type',
            'serial_number' => 'serial number',
            'kode_inventaris' => 'kode inventaris',
            'description' => 'deskripsi',
            'cabang_id' => 'cabang',
            'lokasi' => 'lokasi',
            'kondisi' => 'kondisi',
            'status_kepemilikan' => 'status kepemilikan',
            'is_active' => 'status aktif',
            'evidences.*.name' => 'nama evidence',
            'evidences.*.file' => 'file evidence',
        ];
    }

    public function getKondisiOptionsProperty(): array
    {
        return collect(AlatKondisi::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()])->toArray();
    }

    public function getKepemilikanOptionsProperty(): array
    {
        return collect(AlatStatusKepemilikan::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()])->toArray();
    }

    public function getCabangOptionsProperty(): array
    {
        return Cabang::active()->orderBy('name')->get()->map(fn ($c) => [
            'value' => $c->id,
            'label' => $c->name,
        ])->toArray();
    }

    public function addEvidence()
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

    public function removeEvidence($index)
    {
        if (isset($this->evidences[$index]['id']) && $this->evidences[$index]['id']) {
            $this->deletedEvidenceIds[] = $this->evidences[$index]['id'];
        }

        unset($this->evidences[$index]);
        $this->evidences = array_values($this->evidences);
    }

    public function removeEvidenceFile($index)
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

        $record = $this->alat->evidences()->findOrFail($evidence['id']);

        if (! $record->file_path || $record->file_status !== 'completed') {
            $this->notifyError('File belum tersedia untuk diunduh.');

            return;
        }

        return $fileStorage->download($record->file_path, $record->file_name);
    }

    public function save(AlatService $service, FileStorageService $fileStorage)
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
                'merk_type' => $this->merk_type,
                'serial_number' => $this->serial_number,
                'kode_inventaris' => $this->kode_inventaris ? strtoupper($this->kode_inventaris) : null,
                'description' => $this->description,
                'cabang_id' => $this->cabang_id,
                'lokasi' => $this->lokasi,
                'kondisi' => $this->kondisi,
                'status_kepemilikan' => $this->status_kepemilikan,
                'is_active' => $this->is_active,
            ];

            // Process new evidence uploads
            $newEvidences = [];
            foreach ($this->evidences as $evidence) {
                // Skip existing evidences (already saved)
                if (isset($evidence['id']) && $evidence['id']) {
                    continue;
                }

                if (! isset($evidence['file']) || ! $evidence['file']) {
                    continue;
                }

                $temp = $fileStorage->storeTemp($evidence['file'], 'alat-evidences');
                $newEvidences[] = [
                    'name' => $evidence['name'],
                    'temp_path' => $temp['path'],
                    'original_name' => $temp['original_name'],
                ];
            }

            if ($this->editMode) {
                $alat = Alat::findOrFail($this->alatId);
                $this->authorize('update', $alat);
                $service->update($alat, $data, $newEvidences, $this->deletedEvidenceIds);
                $this->notifySuccess('Alat berhasil diupdate!');

                return $this->redirect(route('master-data.alat.index'), navigate: true);
            } else {
                $this->authorize('create', Alat::class);
                $alat = $service->create($data, $newEvidences);
                $this->notifySuccess('Alat berhasil ditambahkan! Lanjutkan dengan menambah history kalibrasi.');

                return $this->redirect(route('master-data.alat.show', $alat), navigate: true);
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk melakukan aksi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function cancel()
    {
        return $this->redirect(route('master-data.alat.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.master-data.alat-form');
    }
}
