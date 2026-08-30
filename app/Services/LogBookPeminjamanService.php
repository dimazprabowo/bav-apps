<?php

namespace App\Services;

use App\Enums\LogBookStatus;
use App\Models\Alat;
use App\Models\LogBookPeminjaman;
use App\Traits\HasDynamicLike;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LogBookPeminjamanService
{
    use HasDynamicLike;

    /**
     * Apply cabang scoping to a logbook query based on the authenticated user.
     *
     * Users WITHOUT `access_all_cabang` see logbooks where:
     *   - the alat's cabang matches their own cabang (`cabang_id`), OR
     *   - they are the peminjam (`peminjam_id` = their id) — covers cross-cabang requests they made.
     *
     * Users WITH `access_all_cabang` see all logbooks (optionally narrowed by explicit filter).
     */
    protected function applyCabangScope($query, ?int $userCabangId, ?string $explicitCabangFilter = null): void
    {
        $canAccessAll = auth()->check() && auth()->user()->can('access_all_cabang');

        if (! $canAccessAll) {
            $userId = auth()->id();
            $query->where(function ($q) use ($userCabangId, $userId) {
                $q->where('cabang_id', $userCabangId)
                    ->orWhere('peminjam_id', $userId);
            });
        } elseif ($explicitCabangFilter !== null && $explicitCabangFilter !== '') {
            $query->where('cabang_id', $explicitCabangFilter);
        }
    }

    public function getFiltered(
        ?string $search = null,
        ?string $statusFilter = null,
        ?string $cabangFilter = null,
        ?string $alatFilter = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = LogBookPeminjaman::with(['alat.cabang', 'peminjam', 'cabang', 'approver']);

        // Cabang scoping (data-level RBAC)
        $userCabangId = auth()->check() ? auth()->user()->cabang_id : null;
        $this->applyCabangScope($query, $userCabangId, $cabangFilter);

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->whereHas('alat', function ($q) use ($search, $operator) {
                    $q->where('code', $operator, "%{$search}%")
                        ->orWhere('name', $operator, "%{$search}%");
                })
                    ->orWhereHas('peminjam', function ($q) use ($search, $operator) {
                        $q->where('name', $operator, "%{$search}%");
                    })
                    ->orWhere('deskripsi_pekerjaan', $operator, "%{$search}%")
                    ->orWhere('catatan', $operator, "%{$search}%");
            });
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        // Note: cabangFilter is already applied via applyCabangScope() above
        // (ignored for users without access_all_cabang).

        if ($alatFilter !== null && $alatFilter !== '') {
            $query->where('alat_id', $alatFilter);
        }

        return $query->orderBy('tanggal_pinjam', 'desc')->paginate($perPage);
    }

    public function create(array $data): LogBookPeminjaman
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = LogBookStatus::Requested->value;
            $data['created_by'] = auth()->id();

            return LogBookPeminjaman::create($data);
        });
    }

    public function update(LogBookPeminjaman $logBook, array $data): LogBookPeminjaman
    {
        $logBook->update($data);

        return $logBook;
    }

    public function delete(LogBookPeminjaman $logBook): void
    {
        $logBook->delete();
    }

    public function approve(LogBookPeminjaman $logBook, int $approverId): LogBookPeminjaman
    {
        return DB::transaction(function () use ($logBook, $approverId) {
            $logBook->update([
                'status' => LogBookStatus::Approved->value,
                'approved_by' => $approverId,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            return $logBook->fresh();
        });
    }

    public function reject(LogBookPeminjaman $logBook, int $approverId, string $reason): LogBookPeminjaman
    {
        return DB::transaction(function () use ($logBook, $approverId, $reason) {
            $logBook->update([
                'status' => LogBookStatus::Rejected->value,
                'approved_by' => $approverId,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $logBook->fresh();
        });
    }

    public function startBorrowing(LogBookPeminjaman $logBook): LogBookPeminjaman
    {
        return DB::transaction(function () use ($logBook) {
            $logBook->update([
                'status' => LogBookStatus::Borrowed->value,
            ]);

            return $logBook->fresh();
        });
    }

    public function returnAlat(LogBookPeminjaman $logBook, string $kondisiKembali, ?string $catatan = null): LogBookPeminjaman
    {
        return DB::transaction(function () use ($logBook, $kondisiKembali, $catatan) {
            $logBook->update([
                'status' => LogBookStatus::Returned->value,
                'tanggal_kembali_aktual' => today(),
                'kondisi_kembali' => $kondisiKembali,
                'catatan' => $catatan ? $logBook->catatan."\n[Return] ".$catatan : $logBook->catatan,
            ]);

            // Update alat condition if changed
            $alat = $logBook->alat;
            if ($kondisiKembali !== $alat->kondisi->value) {
                $alat->update(['kondisi' => $kondisiKembali]);
            }

            return $logBook->fresh();
        });
    }

    public function cancel(LogBookPeminjaman $logBook, ?string $reason = null): LogBookPeminjaman
    {
        return DB::transaction(function () use ($logBook, $reason) {
            $logBook->update([
                'status' => LogBookStatus::Cancelled->value,
                'cancellation_reason' => $reason,
            ]);

            return $logBook->fresh();
        });
    }

    public function updateOverdueStatus(): int
    {
        return LogBookPeminjaman::where('status', LogBookStatus::Borrowed)
            ->where('tanggal_kembali_rencana', '<', today())
            ->whereNull('tanggal_kembali_aktual')
            ->update(['status' => LogBookStatus::Overdue->value]);
    }

    public function getDashboardStats(): array
    {
        $userCabangId = auth()->check() ? auth()->user()->cabang_id : null;
        $canAccessAll = auth()->check() && auth()->user()->can('access_all_cabang');

        $base = LogBookPeminjaman::query();
        if (! $canAccessAll) {
            $userId = auth()->id();
            $base->where(function ($q) use ($userCabangId, $userId) {
                $q->where('cabang_id', $userCabangId)
                    ->orWhere('peminjam_id', $userId);
            });
        }

        return [
            'total' => (clone $base)->count(),
            'borrowed' => (clone $base)->borrowed()->count(),
            'overdue' => (clone $base)->overdue()->count(),
            'requested' => (clone $base)->requested()->count(),
        ];
    }
}
