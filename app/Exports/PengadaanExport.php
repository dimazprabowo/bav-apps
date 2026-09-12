<?php

namespace App\Exports;

use App\Models\Pengadaan;
use App\Traits\HasDynamicLike;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PengadaanExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable, HasDynamicLike;

    protected ?string $search;

    protected ?string $vendorFilter;

    protected ?string $cabangFilter;

    protected ?string $statusFilter;

    public function __construct(?string $search = null, ?string $vendorFilter = null, ?string $cabangFilter = null, ?string $statusFilter = null)
    {
        $this->search = $search;
        $this->vendorFilter = $vendorFilter;
        $this->cabangFilter = $cabangFilter;
        $this->statusFilter = $statusFilter;
    }

    public function query()
    {
        $query = Pengadaan::with(['vendor', 'cabang', 'invoices.payments'])->withCount('items');

        $canAccessAll = Auth::check() && Auth::user()->can('access_all_cabang');
        if (! $canAccessAll) {
            $query->where('cabang_id', Auth::user()?->cabang_id);
        } elseif ($this->cabangFilter) {
            $query->where('cabang_id', $this->cabangFilter);
        }

        if ($this->search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($operator) {
                $q->where('no_pengadaan', $operator, "%{$this->search}%")
                    ->orWhereHas('vendor', function ($v) use ($operator) {
                        $v->where('name', $operator, "%{$this->search}%");
                    });
            });
        }

        if ($this->vendorFilter) {
            $query->where('vendor_id', $this->vendorFilter);
        }

        if ($this->statusFilter) {
            $query->where('status_approval', $this->statusFilter);
        }

        return $query->orderByDesc('tanggal_pengadaan');
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Pengadaan',
            'Vendor',
            'Cabang',
            'Tanggal Pengadaan',
            'Jumlah Item',
            'Total Biaya',
            'Status Approval',
            'Status Invoice',
            'Status Pembayaran',
        ];
    }

    public function map($pengadaan): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $pengadaan->no_pengadaan,
            $pengadaan->vendor->name,
            $pengadaan->cabang->name ?? 'Pusat',
            $pengadaan->tanggal_pengadaan->format('d/m/Y'),
            $pengadaan->items_count,
            $pengadaan->total_biaya,
            $pengadaan->status_approval->label(),
            $pengadaan->status_invoice->label(),
            $pengadaan->status_pembayaran->label(),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'],
                ],
            ],
        ];
    }
}
