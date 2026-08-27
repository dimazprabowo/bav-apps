<?php

namespace App\Exports;

use App\Models\Alat;
use App\Traits\HasDynamicLike;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlatExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable, HasDynamicLike;

    protected ?string $search;

    protected ?string $cabangFilter;

    protected ?string $kondisiFilter;

    protected ?string $kalibrasiFilter;

    protected ?string $kepemilikanFilter;

    protected ?string $reviewFilter;

    public function __construct(
        ?string $search = null,
        ?string $cabangFilter = null,
        ?string $kondisiFilter = null,
        ?string $kalibrasiFilter = null,
        ?string $kepemilikanFilter = null,
        ?string $reviewFilter = null
    ) {
        $this->search = $search;
        $this->cabangFilter = $cabangFilter;
        $this->kondisiFilter = $kondisiFilter;
        $this->kalibrasiFilter = $kalibrasiFilter;
        $this->kepemilikanFilter = $kepemilikanFilter;
        $this->reviewFilter = $reviewFilter;
    }

    public function query()
    {
        $query = Alat::with(['cabang', 'reviewer', 'kalibrasis' => function ($q) {
            $q->latest('tanggal_kalibrasi')->limit(1);
        }])->withCount('evidences');

        // Cabang scoping (data-level RBAC)
        $user = auth()->user();
        $canAccessAll = $user && $user->can('access_all_cabang');
        if (! $canAccessAll) {
            $query->where('cabang_id', $user?->cabang_id);
        } elseif ($this->cabangFilter) {
            $query->where('cabang_id', $this->cabangFilter);
        }

        if ($this->search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($operator) {
                $q->where('code', $operator, "%{$this->search}%")
                    ->orWhere('name', $operator, "%{$this->search}%")
                    ->orWhere('merk_type', $operator, "%{$this->search}%")
                    ->orWhere('serial_number', $operator, "%{$this->search}%")
                    ->orWhere('kode_inventaris', $operator, "%{$this->search}%")
                    ->orWhere('lokasi', $operator, "%{$this->search}%");
            });
        }

        // Note: cabangFilter already applied via scoping above.
        if ($this->kondisiFilter) {
            $query->where('kondisi', $this->kondisiFilter);
        }
        if ($this->kepemilikanFilter) {
            $query->where('status_kepemilikan', $this->kepemilikanFilter);
        }
        if ($this->reviewFilter) {
            $query->where('review_status', $this->reviewFilter);
        }

        // Kalibrasi filter handled via whereHas on kalibrasis relation
        if ($this->kalibrasiFilter) {
            $today = now()->format('Y-m-d');
            $threshold = now()->addDays(30)->format('Y-m-d');

            switch ($this->kalibrasiFilter) {
                case \App\Enums\AlatStatusKalibrasi::Terkalibrasi->value:
                    $query->whereHas('kalibrasis', fn ($q) => $q->whereNotNull('tanggal_kalibrasi_berikutnya')->where('tanggal_kalibrasi_berikutnya', '>', $threshold));
                    break;
                case \App\Enums\AlatStatusKalibrasi::Expired->value:
                    $query->whereHas('kalibrasis', fn ($q) => $q->whereNotNull('tanggal_kalibrasi_berikutnya')->where('tanggal_kalibrasi_berikutnya', '<', $today));
                    break;
                case \App\Enums\AlatStatusKalibrasi::Pending->value:
                    $query->whereHas('kalibrasis', fn ($q) => $q->whereNotNull('tanggal_kalibrasi_berikutnya')->where('tanggal_kalibrasi_berikutnya', '>=', $today)->where('tanggal_kalibrasi_berikutnya', '<=', $threshold));
                    break;
                case \App\Enums\AlatStatusKalibrasi::TidakPerlu->value:
                    $query->whereDoesntHave('kalibrasis', fn ($q) => $q->whereNotNull('tanggal_kalibrasi_berikutnya'));
                    break;
            }
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode',
            'Nama Alat',
            'Merk/Type',
            'Serial Number',
            'Kode Inventaris',
            'Cabang',
            'Lokasi',
            'Kondisi',
            'Status Kalibrasi',
            'Tanggal Kalibrasi Terakhir',
            'Kalibrasi Berikutnya',
            'Kepemilikan',
            'Status Review',
            'Reviewer',
            'Aktif',
            'Tanggal Dibuat',
        ];
    }

    public function map($alat): array
    {
        static $no = 0;
        $no++;

        $latestKal = $alat->latest_kalibrasi;

        return [
            $no,
            $alat->code,
            $alat->name,
            $alat->merk_type ?? '-',
            $alat->serial_number ?? '-',
            $alat->kode_inventaris ?? '-',
            $alat->cabang?->name ?? '-',
            $alat->lokasi ?? '-',
            $alat->kondisi->label(),
            $alat->status_kalibrasi_derived->label(),
            $latestKal?->tanggal_kalibrasi?->format('d/m/Y') ?? '-',
            $latestKal?->tanggal_kalibrasi_berikutnya?->format('d/m/Y') ?? '-',
            $alat->status_kepemilikan->label(),
            $alat->review_status->label(),
            $alat->reviewer?->name ?? '-',
            $alat->is_active ? 'Ya' : 'Tidak',
            $alat->created_at->format('d/m/Y H:i'),
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
