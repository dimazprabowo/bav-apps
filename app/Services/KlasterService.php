<?php

namespace App\Services;

use App\Enums\KlasterStatus;
use App\Models\Klaster;
use App\Traits\HasDynamicLike;
use Illuminate\Pagination\LengthAwarePaginator;

class KlasterService
{
    use HasDynamicLike;

    public function getFiltered(
        ?string $search = null,
        ?string $statusFilter = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Klaster::withCount('vendors');

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->where('code', $operator, "%{$search}%")
                    ->orWhere('name', $operator, "%{$search}%")
                    ->orWhere('description', $operator, "%{$search}%");
            });
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function create(array $data): Klaster
    {
        $data['code'] = strtoupper($data['code']);

        return Klaster::create($data);
    }

    public function update(Klaster $klaster, array $data): Klaster
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        $klaster->update($data);

        return $klaster;
    }

    public function delete(Klaster $klaster): void
    {
        $klaster->delete();
    }

    public function toggleStatus(Klaster $klaster): Klaster
    {
        $newStatus = $klaster->status === KlasterStatus::Aktif
            ? KlasterStatus::Nonaktif
            : KlasterStatus::Aktif;

        $klaster->update(['status' => $newStatus->value]);

        return $klaster;
    }

    public function getActiveKlasters(): \Illuminate\Database\Eloquent\Collection
    {
        return Klaster::active()->orderBy('name')->get();
    }
}
