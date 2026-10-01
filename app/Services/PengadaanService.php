<?php

namespace App\Services;

use App\Enums\PaymentApprovalStatus;
use App\Enums\PengadaanApprovalStatus;
use App\Enums\TipeBiaya;
use App\Jobs\ProcessPengadaanEvidence;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Pengadaan;
use App\Models\PengadaanEvidence;
use App\Traits\HasDynamicLike;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PengadaanService
{
    use HasDynamicLike;

    /**
     * Apply cabang scoping to a query based on the authenticated user.
     *
     * Users WITHOUT `access_all_cabang` permission only see pengadaan yang cabang_id-nya
     * cocok dengan cabang mereka sendiri. Users WITH the permission (Super Admin, Admin Pusat)
     * see all pengadaan — optionally narrowed by an explicit cabang filter.
     */
    protected function applyCabangScope($query, ?int $userCabangId, ?string $explicitCabangFilter = null): void
    {
        $canAccessAll = auth()->check() && auth()->user()->can('access_all_cabang');

        if (! $canAccessAll) {
            $query->where('cabang_id', $userCabangId);
        } elseif ($explicitCabangFilter !== null && $explicitCabangFilter !== '') {
            $query->where('cabang_id', $explicitCabangFilter);
        }
    }

    public function getFiltered(
        ?string $search = null,
        ?string $vendorFilter = null,
        ?string $cabangFilter = null,
        ?string $statusFilter = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Pengadaan::with(['vendor', 'cabang', 'approver'])->withCount(['items', 'evidences', 'invoices']);

        $userCabangId = auth()->check() ? auth()->user()->cabang_id : null;
        $this->applyCabangScope($query, $userCabangId, $cabangFilter);

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->where('no_pengadaan', $operator, "%{$search}%")
                    ->orWhereHas('vendor', function ($v) use ($search, $operator) {
                        $v->where('name', $operator, "%{$search}%");
                    });
            });
        }

        if ($vendorFilter !== null && $vendorFilter !== '') {
            $query->where('vendor_id', $vendorFilter);
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('status_approval', $statusFilter);
        }

        return $query->orderByDesc('tanggal_pengadaan')->paginate($perPage);
    }

    /**
     * Create a Pengadaan with nested items and evidences in a single transaction.
     *
     * @param  array<int, array{nama_item: string, kategori_item_id: ?int, qty: int, satuan_id: int, harga_satuan: float}>  $items
     * @param  array<int, array{name: string, temp_path: string, original_name: string}>  $evidences
     */
    public function create(array $data, array $items, array $evidences = []): Pengadaan
    {
        return DB::transaction(function () use ($data, $items, $evidences) {
            $data['no_pengadaan'] = strtoupper($data['no_pengadaan']);
            $isRab = ($data['tipe_biaya'] ?? null) === TipeBiaya::RabProject->value;
            $data['nama_project'] = $isRab ? ($data['nama_project'] ?? null) : null;
            $data['no_wbs'] = $isRab ? strtoupper($data['no_wbs'] ?? '') : null;
            $data['status_approval'] = PengadaanApprovalStatus::Pending->value;
            $data['total_biaya'] = 0;

            $pengadaan = Pengadaan::create($data);

            $total = $this->syncItems($pengadaan, $items);
            $pengadaan->update(['total_biaya' => $total]);

            foreach ($evidences as $evidence) {
                $this->createEvidence($pengadaan, $evidence);
            }

            return $pengadaan->fresh(['items', 'evidences', 'vendor', 'cabang']);
        });
    }

    /**
     * Update a Pengadaan header + fully replace its items + apply evidence changes.
     *
     * @param  array<int, array{id: ?int, nama_item: string, kategori_item_id: ?int, qty: int, satuan_id: int, harga_satuan: float}>  $items
     * @param  array<int, array{name: string, temp_path: string, original_name: string}>  $newEvidences
     * @param  array<int>  $deletedEvidenceIds
     */
    public function update(Pengadaan $pengadaan, array $data, array $items, array $newEvidences = [], array $deletedEvidenceIds = []): Pengadaan
    {
        return DB::transaction(function () use ($pengadaan, $data, $items, $newEvidences, $deletedEvidenceIds) {
            if (isset($data['no_pengadaan'])) {
                $data['no_pengadaan'] = strtoupper($data['no_pengadaan']);
            }

            if (isset($data['tipe_biaya'])) {
                $isRab = $data['tipe_biaya'] === TipeBiaya::RabProject->value;
                $data['nama_project'] = $isRab ? ($data['nama_project'] ?? null) : null;
                $data['no_wbs'] = $isRab ? strtoupper($data['no_wbs'] ?? '') : null;
            }

            $pengadaan->update($data);

            $total = $this->syncItems($pengadaan, $items);
            $pengadaan->update(['total_biaya' => $total]);

            foreach ($deletedEvidenceIds as $evidenceId) {
                $this->deleteEvidence($evidenceId);
            }

            foreach ($newEvidences as $evidence) {
                $this->createEvidence($pengadaan, $evidence);
            }

            return $pengadaan->fresh(['items', 'evidences', 'vendor', 'cabang']);
        });
    }

    /**
     * Replace all items belonging to a Pengadaan with the given payload.
     * Computes subtotal per item and returns the new total_biaya.
     */
    protected function syncItems(Pengadaan $pengadaan, array $items): float
    {
        $pengadaan->items()->delete();

        $total = 0;
        foreach ($items as $item) {
            $qty = (int) ($item['qty'] ?? 1);
            $hargaSatuan = (float) ($item['harga_satuan'] ?? 0);
            $subtotal = $qty * $hargaSatuan;
            $total += $subtotal;

            $pengadaan->items()->create([
                'nama_item' => $item['nama_item'],
                'kategori_item_id' => $item['kategori_item_id'] ?? null,
                'qty' => $qty,
                'satuan_id' => $item['satuan_id'],
                'harga_satuan' => $hargaSatuan,
                'subtotal' => $subtotal,
            ]);
        }

        return $total;
    }

    public function delete(Pengadaan $pengadaan): void
    {
        DB::transaction(function () use ($pengadaan) {
            $fileStorage = app(FileStorageService::class);
            foreach ($pengadaan->evidences as $evidence) {
                $fileStorage->delete($evidence->file_path);
            }

            $pengadaan->evidences()->delete();
            $pengadaan->items()->delete();
            $pengadaan->delete();
        });
    }

    public function approve(Pengadaan $pengadaan, int $approverId): Pengadaan
    {
        return DB::transaction(function () use ($pengadaan, $approverId) {
            $pengadaan->update([
                'status_approval' => PengadaanApprovalStatus::Approved->value,
                'approved_by' => $approverId,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            return $pengadaan->fresh();
        });
    }

    public function reject(Pengadaan $pengadaan, int $approverId, string $reason): Pengadaan
    {
        return DB::transaction(function () use ($pengadaan, $approverId, $reason) {
            $pengadaan->update([
                'status_approval' => PengadaanApprovalStatus::Rejected->value,
                'approved_by' => $approverId,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $pengadaan->fresh();
        });
    }

    // ============================================================
    // Evidence Management
    // ============================================================

    protected function createEvidence(Pengadaan $pengadaan, array $evidence): PengadaanEvidence
    {
        $record = PengadaanEvidence::create([
            'pengadaan_id' => $pengadaan->id,
            'name' => $evidence['name'],
            'file_status' => 'processing',
        ]);

        ProcessPengadaanEvidence::dispatch(
            $record->id,
            $evidence['temp_path'],
            $evidence['original_name'],
            Str::slug($pengadaan->no_pengadaan)
        );

        return $record;
    }

    protected function deleteEvidence(int $evidenceId): void
    {
        $evidence = PengadaanEvidence::find($evidenceId);
        if (! $evidence) {
            return;
        }

        app(FileStorageService::class)->delete($evidence->file_path);
        $evidence->delete();
    }

    // ============================================================
    // Dashboard & Reporting
    // ============================================================

    /**
     * Ringkasan biaya, penagihan (invoice), dan pembayaran untuk dashboard.
     * Hanya Pengadaan berstatus Approved yang dihitung ke total biaya (data
     * belum-fix tidak mengotori laporan). Cabang-scoped.
     *
     * Field yang dikembalikan:
     * - total_pengadaan, total_biaya: agregat Pengadaan Approved.
     * - total_invoice, total_belum_ditagih: status penagihan.
     * - total_dibayar, total_outstanding, persentase_dibayar: status pembayaran.
     * - persentase_ditagih: progress penagihan terhadap total biaya.
     * - invoice_total, invoice_lunas, invoice_sebagian, invoice_belum_dibayar:
     *   breakdown status invoice.
     * - invoice_overdue, invoice_due_soon: invoice yang perlu perhatian.
     * - pengadaan_pending, pengadaan_rejected: action items approval.
     * - pembayaran_pending_approval: action items finance.
     */
    public function getDashboardStats(): array
    {
        $today = now()->format('Y-m-d');
        $dueThreshold = now()->addDays(7)->format('Y-m-d');

        $userCabangId = auth()->check() ? auth()->user()->cabang_id : null;

        // Base query: approved pengadaan, cabang-scoped (untuk financial stats)
        $approvedQuery = function () use ($userCabangId) {
            $q = Pengadaan::query()->approved();
            $this->applyCabangScope($q, $userCabangId);

            return $q;
        };

        // Base query: ALL pengadaan, cabang-scoped (untuk status counts)
        $allQuery = function () use ($userCabangId) {
            $q = Pengadaan::query();
            $this->applyCabangScope($q, $userCabangId);

            return $q;
        };

        $totalBiaya = (float) $approvedQuery()->sum('total_biaya');
        $totalPengadaan = (int) $approvedQuery()->count();
        $pengadaanIds = $approvedQuery()->pluck('id');

        // Invoice financial stats
        $totalInvoice = (float) Invoice::whereIn('pengadaan_id', $pengadaanIds)->sum('jumlah');
        $totalDibayar = (float) InvoicePayment::whereHas('invoice', function ($q) use ($pengadaanIds) {
            $q->whereIn('pengadaan_id', $pengadaanIds);
        })->where('status_approval', PaymentApprovalStatus::Approved)->sum('jumlah_bayar');

        // Invoice status breakdown (single query with subquery sum untuk efisiensi)
        $invoices = Invoice::whereIn('pengadaan_id', $pengadaanIds)
            ->withSum(['payments as total_dibayar_approved' => fn ($q) => $q->where('status_approval', PaymentApprovalStatus::Approved->value)], 'jumlah_bayar')
            ->get();

        $invoiceTotal = $invoices->count();
        $invoiceLunas = $invoices->filter(fn ($i) => (float) ($i->total_dibayar_approved ?? 0) >= (float) $i->jumlah && (float) ($i->total_dibayar_approved ?? 0) > 0)->count();
        $invoiceSebagian = $invoices->filter(fn ($i) => (float) ($i->total_dibayar_approved ?? 0) > 0 && (float) ($i->total_dibayar_approved ?? 0) < (float) $i->jumlah)->count();
        $invoiceBelumDibayar = $invoices->filter(fn ($i) => (float) ($i->total_dibayar_approved ?? 0) <= 0)->count();

        // Invoice due/overdue (dari collection yang sudah di-load, cegah N+1)
        $invoiceOverdue = $invoices->filter(fn ($i) => $i->jatuh_tempo && $i->jatuh_tempo->format('Y-m-d') < $today && (float) ($i->total_dibayar_approved ?? 0) < (float) $i->jumlah)->count();
        $invoiceDueSoon = $invoices->filter(fn ($i) => $i->jatuh_tempo && $i->jatuh_tempo->format('Y-m-d') >= $today && $i->jatuh_tempo->format('Y-m-d') <= $dueThreshold && (float) ($i->total_dibayar_approved ?? 0) < (float) $i->jumlah)->count();

        // Action items: pending approvals (cabang-scoped, semua status)
        $pengadaanPending = (int) $allQuery()->pending()->count();
        $pengadaanRejected = (int) $allQuery()->where('status_approval', PengadaanApprovalStatus::Rejected)->count();
        $pembayaranPendingApproval = (int) InvoicePayment::whereHas('invoice', function ($q) use ($pengadaanIds) {
            $q->whereIn('pengadaan_id', $pengadaanIds);
        })->where('status_approval', PaymentApprovalStatus::Pending)->count();

        // Percentages (capped at 100 untuk progress bar)
        $persentaseDibayar = $totalInvoice > 0 ? min(100, round(($totalDibayar / $totalInvoice) * 100, 1)) : 0;
        $persentaseDitagih = $totalBiaya > 0 ? min(100, round(($totalInvoice / $totalBiaya) * 100, 1)) : 0;

        return [
            'total_pengadaan' => $totalPengadaan,
            'total_biaya' => $totalBiaya,
            'total_invoice' => $totalInvoice,
            'total_belum_ditagih' => max($totalBiaya - $totalInvoice, 0),
            'total_dibayar' => $totalDibayar,
            'total_outstanding' => max($totalInvoice - $totalDibayar, 0),
            'persentase_dibayar' => $persentaseDibayar,
            'persentase_ditagih' => $persentaseDitagih,
            'invoice_total' => $invoiceTotal,
            'invoice_lunas' => $invoiceLunas,
            'invoice_sebagian' => $invoiceSebagian,
            'invoice_belum_dibayar' => $invoiceBelumDibayar,
            'invoice_overdue' => $invoiceOverdue,
            'invoice_due_soon' => $invoiceDueSoon,
            'pengadaan_pending' => $pengadaanPending,
            'pengadaan_rejected' => $pengadaanRejected,
            'pembayaran_pending_approval' => $pembayaranPendingApproval,
        ];
    }

    /**
     * Top vendor by total spend (Pengadaan Approved), untuk dashboard.
     * Cabang-scoped seperti getDashboardStats().
     */
    public function getSpendPerVendor(int $limit = 5): array
    {
        $userCabangId = auth()->check() ? auth()->user()->cabang_id : null;

        $query = Pengadaan::query()->approved()
            ->join('vendors', 'pengadaans.vendor_id', '=', 'vendors.id')
            ->select('vendors.name as vendor_name')
            ->selectRaw('SUM(pengadaans.total_biaya) as total_spend')
            ->selectRaw('COUNT(*) as total_pengadaan');

        $this->applyCabangScope($query, $userCabangId);

        return $query->groupBy('vendors.id', 'vendors.name')
            ->orderByDesc('total_spend')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'vendor' => $row->vendor_name,
                'total_spend' => (float) $row->total_spend,
                'total_pengadaan' => (int) $row->total_pengadaan,
            ])
            ->toArray();
    }

    /**
     * Distribusi biaya pengadaan per cabang (Approved), untuk dashboard.
     * HANYA relevan untuk user dengan access_all_cabang.
     */
    public function getSpendPerCabang(): array
    {
        $canAccessAll = auth()->check() && auth()->user()->can('access_all_cabang');

        if (! $canAccessAll) {
            return [];
        }

        $rows = Pengadaan::query()->approved()
            ->join('cabangs', 'pengadaans.cabang_id', '=', 'cabangs.id')
            ->select('cabangs.name as cabang_name')
            ->selectRaw('SUM(pengadaans.total_biaya) as total_spend')
            ->selectRaw('COUNT(*) as total_pengadaan')
            ->groupBy('cabangs.id', 'cabangs.name')
            ->orderByDesc('total_spend')
            ->get();

        return $rows->map(fn ($row) => [
            'cabang' => $row->cabang_name,
            'total_spend' => (float) $row->total_spend,
            'total_pengadaan' => (int) $row->total_pengadaan,
        ])->toArray();
    }
}
