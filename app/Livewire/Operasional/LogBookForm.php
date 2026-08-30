<?php

namespace App\Livewire\Operasional;

use App\Enums\AlatKondisi;
use App\Enums\LogBookStatus;
use App\Livewire\Traits\HasNotification;
use App\Models\Alat;
use App\Models\Cabang;
use App\Models\LogBookPeminjaman;
use App\Models\User;
use App\Services\LogBookPeminjamanService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class LogBookForm extends Component
{
    use AuthorizesRequests, HasNotification;

    public bool $editMode = false;

    public $logId;

    public $alat_id;

    public $peminjam_id;

    public $cabang_id;

    public $tanggal_pinjam;

    public $tanggal_kembali_rencana;

    public $kondisi_pinjam = 'baik';

    public $deskripsi_pekerjaan;

    public $catatan;

    public function mount($logBook = null): void
    {
        if ($logBook) {
            $this->editMode = true;
            $this->logId = $logBook->id;
            $this->authorize('update', $logBook);

            $this->fill([
                'alat_id' => $logBook->alat_id,
                'peminjam_id' => $logBook->peminjam_id,
                'cabang_id' => $logBook->cabang_id,
                'tanggal_pinjam' => $logBook->tanggal_pinjam->format('Y-m-d'),
                'tanggal_kembali_rencana' => $logBook->tanggal_kembali_rencana->format('Y-m-d'),
                'kondisi_pinjam' => $logBook->kondisi_pinjam->value,
                'deskripsi_pekerjaan' => $logBook->deskripsi_pekerjaan,
                'catatan' => $logBook->catatan,
            ]);
        } else {
            $this->authorize('create', LogBookPeminjaman::class);
            $this->tanggal_pinjam = today()->format('Y-m-d');
            $this->peminjam_id = auth()->id();
        }
    }

    public function rules()
    {
        return [
            'alat_id' => 'required|exists:alats,id',
            'peminjam_id' => 'required|exists:users,id',
            'cabang_id' => 'required|exists:cabangs,id',
            'tanggal_pinjam' => 'required|date|after_or_equal:today',
            'tanggal_kembali_rencana' => 'required|date|after_or_equal:tanggal_pinjam',
            'kondisi_pinjam' => ['required', 'string', 'in:'.implode(',', AlatKondisi::values())],
            'deskripsi_pekerjaan' => 'nullable|string|max:255',
            'catatan' => 'nullable|string|max:2000',
        ];
    }

    public function validationAttributes()
    {
        return [
            'alat_id' => 'alat',
            'peminjam_id' => 'peminjam',
            'cabang_id' => 'cabang',
            'tanggal_pinjam' => 'tanggal pinjam',
            'tanggal_kembali_rencana' => 'tanggal kembali rencana',
            'kondisi_pinjam' => 'kondisi pinjam',
            'deskripsi_pekerjaan' => 'deskripsi pekerjaan',
            'catatan' => 'catatan',
        ];
    }

    public function getKondisiOptionsProperty(): array
    {
        return collect(AlatKondisi::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()])->toArray();
    }

    public function getCabangOptionsProperty(): array
    {
        return Cabang::active()->orderBy('name')->get()->map(fn ($c) => [
            'value' => $c->id,
            'label' => $c->name,
        ])->toArray();
    }

    public function getAlatOptionsProperty(): array
    {
        if (! $this->cabang_id) {
            return [];
        }

        // Tampilkan SEMUA alat available (is_active + approved) di cabang terpilih.
        // Alat yang sedang dipinjam/diajukan tetap muncul (sebagai info) tapi ditandai
        // disabled + sublabel status — tidak bisa dipilih (anti-double-booking).
        $activeStatuses = [
            LogBookStatus::Requested,
            LogBookStatus::Approved,
            LogBookStatus::Borrowed,
            LogBookStatus::Overdue,
        ];

        return Alat::available()
            ->where('cabang_id', $this->cabang_id)
            ->with(['logBookPeminjaman' => function ($q) use ($activeStatuses) {
                $q->whereIn('status', $activeStatuses);
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($a) use ($activeStatuses) {
                $isBorrowed = $a->logBookPeminjaman
                    ->filter(fn ($log) => in_array($log->status, $activeStatuses))
                    ->reject(fn ($log) => $this->editMode && $log->id === (int) $this->logId)
                    ->isNotEmpty();

                return [
                    'value' => $a->id,
                    'label' => "{$a->code} - {$a->name}",
                    'disabled' => $isBorrowed,
                    'sublabel' => $isBorrowed ? 'Sedang dipinjam/diajukan' : null,
                ];
            })
            ->toArray();
    }

    public function getPeminjamOptionsProperty(): array
    {
        $user = auth()->user();
        $canAccessAll = $user && $user->can('access_all_cabang');

        $query = User::active()->orderBy('name');

        if (! $canAccessAll && $user?->cabang_id) {
            // Scope peminjam ke cabang user pembuat pengajuan.
            $query->where('cabang_id', $user->cabang_id);
        }

        return $query->get()->map(fn ($u) => [
            'value' => $u->id,
            'label' => $u->name,
        ])->toArray();
    }

    public function updatedCabangId()
    {
        $this->alat_id = null;
    }

    public function save(LogBookPeminjamanService $service)
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->notifyValidationError($e);
            throw $e;
        }

        try {
            $data = [
                'alat_id' => $this->alat_id,
                'peminjam_id' => $this->peminjam_id,
                'cabang_id' => $this->cabang_id,
                'tanggal_pinjam' => $this->tanggal_pinjam,
                'tanggal_kembali_rencana' => $this->tanggal_kembali_rencana,
                'kondisi_pinjam' => $this->kondisi_pinjam,
                'deskripsi_pekerjaan' => $this->deskripsi_pekerjaan,
                'catatan' => $this->catatan,
            ];

            if ($this->editMode) {
                $log = LogBookPeminjaman::findOrFail($this->logId);
                $this->authorize('update', $log);
                $service->update($log, $data);
                $message = 'LogBook berhasil diupdate!';
            } else {
                $this->authorize('create', LogBookPeminjaman::class);
                $service->create($data);
                $message = 'Peminjaman alat berhasil diajukan! Menunggu approval.';
            }

            $this->notifySuccess($message);

            return $this->redirect(route('operasional.logbook.index'), navigate: true);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk melakukan aksi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function cancel()
    {
        return $this->redirect(route('operasional.logbook.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.operasional.logbook-form');
    }
}
