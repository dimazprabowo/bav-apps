<?php

namespace App\Services;

use App\Enums\CabangStatus;
use App\Models\Cabang;
use App\Traits\HasDynamicLike;
use Illuminate\Pagination\LengthAwarePaginator;

class CabangService
{
    use HasDynamicLike;

    public function getFiltered(
        ?string $search = null,
        ?string $statusFilter = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Cabang::query();

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->where('code', $operator, "%{$search}%")
                    ->orWhere('name', $operator, "%{$search}%")
                    ->orWhere('address', $operator, "%{$search}%")
                    ->orWhere('phone', $operator, "%{$search}%")
                    ->orWhere('pic_name', $operator, "%{$search}%");
            });
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function create(array $data): Cabang
    {
        return Cabang::create($data);
    }

    public function update(Cabang $cabang, array $data): Cabang
    {
        $cabang->update($data);

        return $cabang;
    }

    public function delete(Cabang $cabang): void
    {
        $cabang->delete();
    }

    public function toggleStatus(Cabang $cabang): Cabang
    {
        $newStatus = $cabang->status === CabangStatus::Active
            ? CabangStatus::Inactive
            : CabangStatus::Active;

        $cabang->update(['status' => $newStatus->value]);

        return $cabang;
    }

    public function getActiveCabang(): \Illuminate\Database\Eloquent\Collection
    {
        return Cabang::active()->orderBy('name')->get();
    }
}
