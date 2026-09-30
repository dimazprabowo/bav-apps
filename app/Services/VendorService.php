<?php

namespace App\Services;

use App\Enums\VendorStatus;
use App\Models\Vendor;
use App\Traits\HasDynamicLike;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VendorService
{
    use HasDynamicLike;

    public function getFiltered(
        ?string $search = null,
        ?string $statusFilter = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Vendor::with('klaster')->withCount(['pengadaans', 'kategoriItems']);

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->where('code', $operator, "%{$search}%")
                    ->orWhere('name', $operator, "%{$search}%")
                    ->orWhere('contact_person', $operator, "%{$search}%")
                    ->orWhere('phone', $operator, "%{$search}%")
                    ->orWhere('email', $operator, "%{$search}%");
            });
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function create(array $data, array $kategoriItemIds = []): Vendor
    {
        $data['code'] = strtoupper($data['code']);

        return DB::transaction(function () use ($data, $kategoriItemIds) {
            $vendor = Vendor::create($data);
            $vendor->kategoriItems()->sync($kategoriItemIds);

            return $vendor;
        });
    }

    public function update(Vendor $vendor, array $data, ?array $kategoriItemIds = null): Vendor
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        return DB::transaction(function () use ($vendor, $data, $kategoriItemIds) {
            $vendor->update($data);

            if ($kategoriItemIds !== null) {
                $vendor->kategoriItems()->sync($kategoriItemIds);
            }

            return $vendor;
        });
    }

    public function delete(Vendor $vendor): void
    {
        $vendor->delete();
    }

    public function toggleStatus(Vendor $vendor): Vendor
    {
        $newStatus = $vendor->status === VendorStatus::Aktif
            ? VendorStatus::Nonaktif
            : VendorStatus::Aktif;

        $vendor->update(['status' => $newStatus->value]);

        return $vendor;
    }

    public function getActiveVendors(): \Illuminate\Database\Eloquent\Collection
    {
        return Vendor::active()->orderBy('name')->get();
    }
}
