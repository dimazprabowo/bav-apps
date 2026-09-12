<?php

namespace App\Services;

use App\Enums\VendorStatus;
use App\Models\Vendor;
use App\Traits\HasDynamicLike;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorService
{
    use HasDynamicLike;

    public function getFiltered(
        ?string $search = null,
        ?string $statusFilter = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Vendor::withCount('pengadaans');

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

    public function create(array $data): Vendor
    {
        $data['code'] = strtoupper($data['code']);

        return Vendor::create($data);
    }

    public function update(Vendor $vendor, array $data): Vendor
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        $vendor->update($data);

        return $vendor;
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
