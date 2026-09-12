<?php

namespace App\Models;

use App\Enums\PengadaanApprovalStatus;
use App\Enums\StatusInvoicePengadaan;
use App\Enums\StatusPembayaranPengadaan;
use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Pengadaan extends Model
{
    use HasEncryptedRouteKey, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'no_pengadaan',
        'vendor_id',
        'cabang_id',
        'tanggal_pengadaan',
        'total_biaya',
        'status_approval',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pengadaan' => 'date',
        'total_biaya' => 'decimal:2',
        'status_approval' => PengadaanApprovalStatus::class,
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'no_pengadaan', 'vendor_id', 'cabang_id', 'tanggal_pengadaan',
                'total_biaya', 'status_approval', 'rejection_reason',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('pengadaan');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PengadaanItem::class);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(PengadaanEvidence::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderBy('tanggal_invoice', 'desc');
    }

    public function scopeApproved($query)
    {
        return $query->where('status_approval', PengadaanApprovalStatus::Approved);
    }

    public function scopePending($query)
    {
        return $query->where('status_approval', PengadaanApprovalStatus::Pending);
    }

    public function isPending(): bool
    {
        return $this->status_approval === PengadaanApprovalStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status_approval === PengadaanApprovalStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status_approval === PengadaanApprovalStatus::Rejected;
    }

    /**
     * Total invoice yang sudah diterbitkan vendor untuk pengadaan ini.
     */
    public function getTotalInvoiceAttribute(): float
    {
        if ($this->relationLoaded('invoices')) {
            return (float) $this->invoices->sum('jumlah');
        }

        return (float) $this->invoices()->sum('jumlah');
    }

    /**
     * Status invoice derived: belum ditagih / ditagih sebagian / sudah ditagih penuh.
     */
    public function getStatusInvoiceAttribute(): StatusInvoicePengadaan
    {
        $totalInvoice = $this->total_invoice;

        if ($totalInvoice <= 0) {
            return StatusInvoicePengadaan::BelumDitagih;
        }

        if ($totalInvoice < (float) $this->total_biaya) {
            return StatusInvoicePengadaan::DitagihSebagian;
        }

        return StatusInvoicePengadaan::SudahDitagihPenuh;
    }

    /**
     * Status pembayaran derived, agregat dari status pembayaran semua invoice
     * milik pengadaan ini.
     */
    public function getStatusPembayaranAttribute(): StatusPembayaranPengadaan
    {
        $invoices = $this->relationLoaded('invoices') ? $this->invoices : $this->invoices()->with('payments')->get();

        if ($invoices->isEmpty()) {
            return StatusPembayaranPengadaan::BelumDibayar;
        }

        $totalJumlah = $invoices->sum('jumlah');
        $totalDibayar = $invoices->sum('total_dibayar');

        if ($totalDibayar <= 0) {
            return StatusPembayaranPengadaan::BelumDibayar;
        }

        if ($totalDibayar >= $totalJumlah) {
            return StatusPembayaranPengadaan::Lunas;
        }

        return StatusPembayaranPengadaan::Sebagian;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
