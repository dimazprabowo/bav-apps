<?php

namespace App\Services;

use App\Enums\AlatReviewStatus;
use App\Enums\AlatStatusKalibrasi;
use App\Jobs\ProcessAlatEvidence;
use App\Jobs\ProcessAlatKalibrasi;
use App\Models\Alat;
use App\Models\AlatEvidence;
use App\Models\AlatKalibrasi;
use App\Traits\HasDynamicLike;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AlatService
{
    use HasDynamicLike;

    /**
     * Apply cabang scoping to a query based on the authenticated user.
     *
     * Users WITHOUT `access_all_cabang` permission only see alat in their own
     * cabang (`cabang_id`). Users WITH the permission (Super Admin, Admin Pusat)
     * see all alat — optionally narrowed by an explicit cabang filter.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int|null  $userCabangId  Cabang ID of the authenticated user.
     * @param  string|null  $explicitCabangFilter  Optional explicit filter from UI.
     */
    protected function applyCabangScope($query, ?int $userCabangId, ?string $explicitCabangFilter = null): void
    {
        $canAccessAll = auth()->check() && auth()->user()->can('access_all_cabang');

        if (! $canAccessAll) {
            // Scope strictly to the user's own cabang.
            $query->where('cabang_id', $userCabangId);
        } elseif ($explicitCabangFilter !== null && $explicitCabangFilter !== '') {
            // User with all-cabang access + explicit filter.
            $query->where('cabang_id', $explicitCabangFilter);
        }
    }

    public function getFiltered(
        ?string $search = null,
        ?string $cabangFilter = null,
        ?string $kondisiFilter = null,
        ?string $kalibrasiFilter = null,
        ?string $kepemilikanFilter = null,
        ?string $reviewFilter = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Alat::with(['cabang', 'reviewer', 'kalibrasis' => function ($q) {
            $q->latest('tanggal_kalibrasi')->limit(1);
        }])
            ->withCount('evidences');

        // Cabang scoping (data-level RBAC)
        $userCabangId = auth()->check() ? auth()->user()->cabang_id : null;
        $this->applyCabangScope($query, $userCabangId, $cabangFilter);

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->where('code', $operator, "%{$search}%")
                    ->orWhere('name', $operator, "%{$search}%")
                    ->orWhere('merk_type', $operator, "%{$search}%")
                    ->orWhere('serial_number', $operator, "%{$search}%")
                    ->orWhere('kode_inventaris', $operator, "%{$search}%")
                    ->orWhere('lokasi', $operator, "%{$search}%")
                    ->orWhere('description', $operator, "%{$search}%");
            });
        }

        // Note: cabangFilter is already applied via applyCabangScope() above
        // (it is ignored for users without access_all_cabang).

        if ($kondisiFilter !== null && $kondisiFilter !== '') {
            $query->where('kondisi', $kondisiFilter);
        }

        if ($kalibrasiFilter !== null && $kalibrasiFilter !== '') {
            // Filter by derived status kalibrasi from latest kalibrasi record.
            // We use a subquery to find alats whose latest kalibrasi matches the filter.
            $this->applyKalibrasiFilter($query, $kalibrasiFilter);
        }

        if ($kepemilikanFilter !== null && $kepemilikanFilter !== '') {
            $query->where('status_kepemilikan', $kepemilikanFilter);
        }

        if ($reviewFilter !== null && $reviewFilter !== '') {
            $query->where('review_status', $reviewFilter);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Apply kalibrasi filter based on derived status from latest kalibrasi record.
     */
    protected function applyKalibrasiFilter($query, string $filter): void
    {
        $today = now()->format('Y-m-d');
        $threshold = now()->addDays(30)->format('Y-m-d');

        switch ($filter) {
            case AlatStatusKalibrasi::Terkalibrasi->value:
                // Has kalibrasi with berikutnya > today+30
                $query->whereHas('kalibrasis', function ($q) use ($threshold) {
                    $q->whereNotNull('tanggal_kalibrasi_berikutnya')
                        ->where('tanggal_kalibrasi_berikutnya', '>', $threshold);
                });
                break;
            case AlatStatusKalibrasi::Expired->value:
                // Has kalibrasi with berikutnya < today (expired)
                $query->whereHas('kalibrasis', function ($q) use ($today) {
                    $q->whereNotNull('tanggal_kalibrasi_berikutnya')
                        ->where('tanggal_kalibrasi_berikutnya', '<', $today);
                });
                break;
            case AlatStatusKalibrasi::Pending->value:
                // Has kalibrasi with berikutnya between today and today+30
                $query->whereHas('kalibrasis', function ($q) use ($today, $threshold) {
                    $q->whereNotNull('tanggal_kalibrasi_berikutnya')
                        ->where('tanggal_kalibrasi_berikutnya', '>=', $today)
                        ->where('tanggal_kalibrasi_berikutnya', '<=', $threshold);
                });
                break;
            case AlatStatusKalibrasi::TidakPerlu->value:
                // No kalibrasi records OR latest has no berikutnya date
                $query->whereDoesntHave('kalibrasis', function ($q) {
                    $q->whereNotNull('tanggal_kalibrasi_berikutnya');
                });
                break;
        }
    }

    public function create(array $data, array $evidences = []): Alat
    {
        return DB::transaction(function () use ($data, $evidences) {
            $data['code'] = strtoupper($data['code']);
            $data['review_status'] = AlatReviewStatus::Pending->value;

            $alat = Alat::create($data);

            foreach ($evidences as $evidence) {
                $this->createEvidence($alat, $evidence);
            }

            return $alat;
        });
    }

    public function update(Alat $alat, array $data, array $evidences = [], array $deletedEvidenceIds = []): Alat
    {
        return DB::transaction(function () use ($alat, $data, $evidences, $deletedEvidenceIds) {
            if (isset($data['code'])) {
                $data['code'] = strtoupper($data['code']);
            }

            $alat->update($data);

            // Delete removed evidences
            if (! empty($deletedEvidenceIds)) {
                foreach ($deletedEvidenceIds as $evidenceId) {
                    $this->deleteEvidence($evidenceId);
                }
            }

            // Add new evidences
            foreach ($evidences as $evidence) {
                $this->createEvidence($alat, $evidence);
            }

            return $alat->fresh(['evidences', 'cabang']);
        });
    }

    public function delete(Alat $alat): void
    {
        DB::transaction(function () use ($alat) {
            // Delete evidence files
            $fileStorage = app(FileStorageService::class);
            foreach ($alat->evidences as $evidence) {
                $fileStorage->delete($evidence->file_path);
            }

            // Delete kalibrasi files
            foreach ($alat->kalibrasis as $kalibrasi) {
                $fileStorage->delete($kalibrasi->file_path);
            }

            $alat->evidences()->delete();
            $alat->kalibrasis()->delete();
            $alat->delete();
        });
    }

    public function toggleStatus(Alat $alat): Alat
    {
        $alat->update(['is_active' => ! $alat->is_active]);

        return $alat;
    }

    public function approveReview(Alat $alat, int $reviewerId, ?string $note = null): Alat
    {
        return DB::transaction(function () use ($alat, $reviewerId, $note) {
            $alat->update([
                'review_status' => AlatReviewStatus::Approved->value,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'approval_note' => $note,
                'rejection_reason' => null,
            ]);

            return $alat->fresh();
        });
    }

    public function rejectReview(Alat $alat, int $reviewerId, string $reason): Alat
    {
        return DB::transaction(function () use ($alat, $reviewerId, $reason) {
            $alat->update([
                'review_status' => AlatReviewStatus::Rejected->value,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
                'approval_note' => null,
            ]);

            return $alat->fresh();
        });
    }

    // ============================================================
    // Evidence Management
    // ============================================================

    protected function createEvidence(Alat $alat, array $evidence): AlatEvidence
    {
        $record = AlatEvidence::create([
            'alat_id' => $alat->id,
            'name' => $evidence['name'],
            'file_status' => 'processing',
        ]);

        ProcessAlatEvidence::dispatch(
            $record->id,
            $evidence['temp_path'],
            $evidence['original_name'],
            $alat->slug ?? Str::slug($alat->name)
        );

        return $record;
    }

    protected function deleteEvidence(int $evidenceId): void
    {
        $evidence = AlatEvidence::find($evidenceId);
        if (! $evidence) {
            return;
        }

        app(FileStorageService::class)->delete($evidence->file_path);
        $evidence->delete();
    }

    // ============================================================
    // Kalibrasi Management (CRUD via AlatDetail)
    // ============================================================

    public function createKalibrasi(Alat $alat, array $data, ?array $file = null): AlatKalibrasi
    {
        return DB::transaction(function () use ($alat, $data, $file) {
            $record = AlatKalibrasi::create([
                'alat_id' => $alat->id,
                'tanggal_kalibrasi' => $data['tanggal_kalibrasi'],
                'tanggal_kalibrasi_berikutnya' => $data['tanggal_kalibrasi_berikutnya'] ?? null,
                'vendor' => $data['vendor'] ?? null,
                'sertifikat_no' => isset($data['sertifikat_no']) ? strtoupper($data['sertifikat_no']) : null,
                'hasil' => $data['hasil'] ?? 'lulus',
                'catatan' => $data['catatan'] ?? null,
                'file_status' => $file ? 'processing' : null,
            ]);

            if ($file && isset($file['temp_path'])) {
                ProcessAlatKalibrasi::dispatch(
                    $record->id,
                    $file['temp_path'],
                    $file['original_name'],
                    $alat->slug ?? Str::slug($alat->name)
                );
            }

            return $record;
        });
    }

    public function updateKalibrasi(AlatKalibrasi $kalibrasi, array $data): AlatKalibrasi
    {
        return DB::transaction(function () use ($kalibrasi, $data) {
            if (isset($data['sertifikat_no'])) {
                $data['sertifikat_no'] = strtoupper($data['sertifikat_no']);
            }

            $kalibrasi->update($data);

            return $kalibrasi->fresh();
        });
    }

    public function deleteKalibrasi(AlatKalibrasi $kalibrasi): void
    {
        DB::transaction(function () use ($kalibrasi) {
            app(FileStorageService::class)->delete($kalibrasi->file_path);
            $kalibrasi->delete();
        });
    }

    public function getDashboardStats(): array
    {
        $today = now()->format('Y-m-d');
        $threshold = now()->addDays(30)->format('Y-m-d');

        // Base query with optional cabang scoping
        $scopedQuery = function () {
            $q = Alat::query();
            $userCabangId = auth()->check() ? auth()->user()->cabang_id : null;
            $this->applyCabangScope($q, $userCabangId);

            return $q;
        };

        $base = $scopedQuery();

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->active()->count(),
            'approved' => (clone $base)->approved()->count(),
            'pending_review' => (clone $base)->where('review_status', AlatReviewStatus::Pending)->count(),
            'calibration_expired' => (clone $base)->whereHas('kalibrasis', function ($q) use ($today) {
                $q->whereNotNull('tanggal_kalibrasi_berikutnya')
                    ->where('tanggal_kalibrasi_berikutnya', '<', $today);
            })->count(),
            'calibration_pending' => (clone $base)->whereHas('kalibrasis', function ($q) use ($today, $threshold) {
                $q->whereNotNull('tanggal_kalibrasi_berikutnya')
                    ->where('tanggal_kalibrasi_berikutnya', '>=', $today)
                    ->where('tanggal_kalibrasi_berikutnya', '<=', $threshold);
            })->count(),
            'expiring_soon' => (clone $base)->whereHas('kalibrasis', function ($q) use ($today, $threshold) {
                $q->whereNotNull('tanggal_kalibrasi_berikutnya')
                    ->where('tanggal_kalibrasi_berikutnya', '>=', $today)
                    ->where('tanggal_kalibrasi_berikutnya', '<=', $threshold);
            })->count(),
        ];
    }
}
