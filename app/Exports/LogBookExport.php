<?php

namespace App\Exports;

use App\Models\LogBookPeminjaman;
use App\Traits\HasDynamicLike;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LogBookExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable, HasDynamicLike;

    protected ?string $search;

    protected ?string $statusFilter;

    protected ?string $cabangFilter;

    protected ?string $alatFilter;

    public function __construct(
        ?string $search = null,
        ?string $statusFilter = null,
        ?string $cabangFilter = null,
        ?string $alatFilter = null
    ) {
        $this->search = $search;
        $this->statusFilter = $statusFilter;
        $this->cabangFilter = $cabangFilter;
        $this->alatFilter = $alatFilter;
    }

    public function query()
    {
        $query = LogBookPeminjaman::with(['alat.cabang', 'peminjam', 'cabang', 'approver']);

        // Cabang scoping (data-level RBAC)
        $user = auth()->user();
        $canAccessAll = $user && $user->can('access_all_cabang');
        if (! $canAccessAll) {
            $userId = $user?->id;
            $userCabangId = $user?->cabang_id;
            $query->where(function ($q) use ($userCabangId, $userId) {
                $q->where('cabang_id', $userCabangId)
                    ->orWhere('peminjam_id', $userId);
            });
        } elseif ($this->cabangFilter) {
            $query->where('cabang_id', $this->cabangFilter);
        }

        if ($this->search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($operator) {
                $q->whereHas('alat', function ($q) use ($operator) {
                    $q->where('code', $operator, "%{$this->search}%")
                        ->orWhere('name', $operator, "%{$this->search}%");
                })
                    ->orWhereHas('peminjam', function ($q) use ($operator) {
                        $q->where('name', $operator, "%{$this->search}%");
                    });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        // Note: cabangFilter already applied via scoping above.
        if ($this->alatFilter) {
            $query->where('alat_id', $this->alatFilter);
        }

        return $query->orderBy('tanggal_pinjam', 'desc');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Alat',
            'Nama Alat',
            'Peminjam',
            'Cabang',
            'Tanggal Pinjam',
            'Rencana Kembali',
            'Aktual Kembali',
            'Status',
            'Kondisi Pinjam',
            'Kondisi Kembali',
            'Approver',
            'Catatan',
        ];
    }

    public function map($log): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $log->alat?->code ?? '-',
            $log->alat?->name ?? '-',
            $log->peminjam?->name ?? '-',
            $log->cabang?->name ?? '-',
            $log->tanggal_pinjam->format('d/m/Y'),
            $log->tanggal_kembali_rencana->format('d/m/Y'),
            $log->tanggal_kembali_aktual?->format('d/m/Y') ?? '-',
            $log->status->label(),
            $log->kondisi_pinjam->label(),
            $log->kondisi_kembali?->label() ?? '-',
            $log->approver?->name ?? '-',
            $log->catatan ?? '-',
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
