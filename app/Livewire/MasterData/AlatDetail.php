<?php

namespace App\Livewire\MasterData;

use App\Enums\KalibrasiHasil;
use App\Livewire\Traits\HasNotification;
use App\Models\Alat;
use App\Services\AlatService;
use App\Services\FileStorageService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithFileUploads;

class AlatDetail extends Component
{
    use AuthorizesRequests, HasNotification, WithFileUploads;

    public Alat $alat;

    // Kalibrasi modal state
    public bool $showKalibrasiModal = false;

    public ?int $editingKalibrasiId = null;

    public $kal_tanggal;

    public $kal_tanggal_berikutnya;

    public $kal_vendor;

    public $kal_sertifikat_no;

    public $kal_hasil = 'lulus';

    public $kal_catatan;

    public $kal_file;

    // Delete kalibrasi modal state
    public bool $showDeleteKalibrasiModal = false;

    public ?int $deleteKalibrasiId = null;

    public function mount(Alat $alat): void
    {
        $this->authorize('view', $alat);
        $this->alat = $alat->load([
            'cabang', 'reviewer', 'evidences',
            'kalibrasis' => fn ($q) => $q->orderBy('tanggal_kalibrasi', 'desc'),
            'logBookPeminjaman' => fn ($q) => $q->with(['peminjam', 'cabang', 'approver'])
                ->orderBy('tanggal_pinjam', 'desc'),
        ]);
    }

    // ============================================================
    // Evidence Download
    // ============================================================

    public function downloadEvidence($evidenceId, FileStorageService $fileStorage)
    {
        $evidence = $this->alat->evidences()->findOrFail($evidenceId);

        if (! $evidence->file_path || $evidence->file_status !== 'completed') {
            $this->notifyError('File belum tersedia untuk diunduh.');

            return;
        }

        return $fileStorage->download($evidence->file_path, $evidence->file_name);
    }

    // ============================================================
    // Kalibrasi CRUD
    // ============================================================

    public function getHasilOptionsProperty(): array
    {
        return collect(KalibrasiHasil::cases())->map(fn ($h) => [
            'value' => $h->value,
            'label' => $h->label(),
        ])->toArray();
    }

    public function openCreateKalibrasiModal(): void
    {
        $this->authorize('update', $this->alat);

        $this->resetKalibrasiForm();
        $this->editingKalibrasiId = null;
        $this->showKalibrasiModal = true;
    }

    public function openEditKalibrasiModal(int $kalibrasiId): void
    {
        $this->authorize('update', $this->alat);

        $kalibrasi = $this->alat->kalibrasis()->findOrFail($kalibrasiId);

        $this->editingKalibrasiId = $kalibrasiId;
        $this->kal_tanggal = $kalibrasi->tanggal_kalibrasi?->format('Y-m-d');
        $this->kal_tanggal_berikutnya = $kalibrasi->tanggal_kalibrasi_berikutnya?->format('Y-m-d');
        $this->kal_vendor = $kalibrasi->vendor;
        $this->kal_sertifikat_no = $kalibrasi->sertifikat_no;
        $this->kal_hasil = $kalibrasi->hasil->value;
        $this->kal_catatan = $kalibrasi->catatan;
        $this->kal_file = null;
        $this->showKalibrasiModal = true;
    }

    public function closeKalibrasiModal(): void
    {
        $this->showKalibrasiModal = false;
        $this->resetKalibrasiForm();
    }

    public function removeKalibrasiFile(): void
    {
        $this->kal_file = null;
        $this->resetErrorBag('kal_file');
    }

    protected function resetKalibrasiForm(): void
    {
        $this->kal_tanggal = null;
        $this->kal_tanggal_berikutnya = null;
        $this->kal_vendor = null;
        $this->kal_sertifikat_no = null;
        $this->kal_hasil = 'lulus';
        $this->kal_catatan = null;
        $this->kal_file = null;
        $this->resetErrorBag();
    }

    public function rules(): array
    {
        return [
            'kal_tanggal' => 'required|date',
            'kal_tanggal_berikutnya' => 'nullable|date|after_or_equal:kal_tanggal',
            'kal_vendor' => 'nullable|string|max:255',
            'kal_sertifikat_no' => 'nullable|string|max:100',
            'kal_hasil' => ['required', 'string', 'in:'.implode(',', KalibrasiHasil::values())],
            'kal_catatan' => 'nullable|string|max:2000',
            'kal_file' => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png',
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'kal_tanggal' => 'tanggal kalibrasi',
            'kal_tanggal_berikutnya' => 'tanggal kalibrasi berikutnya',
            'kal_vendor' => 'vendor',
            'kal_sertifikat_no' => 'nomor sertifikat',
            'kal_hasil' => 'hasil kalibrasi',
            'kal_catatan' => 'catatan',
            'kal_file' => 'file sertifikat',
        ];
    }

    public function saveKalibrasi(AlatService $service, FileStorageService $fileStorage): void
    {
        $this->authorize('update', $this->alat);

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->notifyValidationError($e);
            throw $e;
        }

        try {
            $data = [
                'tanggal_kalibrasi' => $this->kal_tanggal,
                'tanggal_kalibrasi_berikutnya' => $this->kal_tanggal_berikutnya,
                'vendor' => $this->kal_vendor,
                'sertifikat_no' => $this->kal_sertifikat_no,
                'hasil' => $this->kal_hasil,
                'catatan' => $this->kal_catatan,
            ];

            $filePayload = null;
            if ($this->kal_file) {
                $temp = $fileStorage->storeTemp($this->kal_file, 'alat-kalibrasi');
                $filePayload = [
                    'temp_path' => $temp['path'],
                    'original_name' => $temp['original_name'],
                ];
            }

            if ($this->editingKalibrasiId) {
                $kalibrasi = $this->alat->kalibrasis()->findOrFail($this->editingKalibrasiId);
                $service->updateKalibrasi($kalibrasi, $data);

                // If new file uploaded, dispatch processing (replace old file)
                if ($filePayload) {
                    $fileStorage->delete($kalibrasi->file_path);
                    $kalibrasi->update(['file_status' => 'processing', 'file_error' => null]);
                    \App\Jobs\ProcessAlatKalibrasi::dispatch(
                        $kalibrasi->id,
                        $filePayload['temp_path'],
                        $filePayload['original_name'],
                        \Illuminate\Support\Str::slug($this->alat->name)
                    );
                }

                $this->notifySuccess('Data kalibrasi berhasil diupdate!');
            } else {
                $service->createKalibrasi($this->alat, $data, $filePayload);
                $this->notifySuccess('Kalibrasi berhasil ditambahkan!');
            }

            $this->closeKalibrasiModal();
            $this->alat->load('kalibrasis');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk melakukan aksi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmDeleteKalibrasi(int $kalibrasiId): void
    {
        $this->authorize('update', $this->alat);

        $this->deleteKalibrasiId = $kalibrasiId;
        $this->showDeleteKalibrasiModal = true;
    }

    public function cancelDeleteKalibrasi(): void
    {
        $this->showDeleteKalibrasiModal = false;
        $this->deleteKalibrasiId = null;
    }

    public function deleteKalibrasi(AlatService $service): void
    {
        $this->authorize('update', $this->alat);

        if (! $this->deleteKalibrasiId) {
            $this->notifyError('Data kalibrasi tidak ditemukan.');

            return;
        }

        try {
            $kalibrasi = $this->alat->kalibrasis()->findOrFail($this->deleteKalibrasiId);
            $service->deleteKalibrasi($kalibrasi);

            $this->notifySuccess('Data kalibrasi berhasil dihapus!');
            $this->cancelDeleteKalibrasi();
            $this->alat->load('kalibrasis');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function downloadKalibrasiFile(int $kalibrasiId, FileStorageService $fileStorage)
    {
        $kalibrasi = $this->alat->kalibrasis()->findOrFail($kalibrasiId);

        if (! $kalibrasi->file_path || $kalibrasi->file_status !== 'completed') {
            $this->notifyError('File sertifikat belum tersedia untuk diunduh.');

            return;
        }

        return $fileStorage->download($kalibrasi->file_path, $kalibrasi->file_name);
    }

    public function goBack()
    {
        return $this->redirect(route('master-data.alat.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.master-data.alat-detail');
    }
}
