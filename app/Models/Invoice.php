<?php

namespace App\Models;

use App\Enums\FileStatus;
use App\Enums\PaymentApprovalStatus;
use App\Enums\StatusPembayaranPengadaan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'pengadaan_id',
        'no_invoice',
        'tanggal_invoice',
        'jumlah',
        'jatuh_tempo',
        'catatan',
        'file_path',
        'file_name',
        'file_size',
        'file_status',
        'file_error',
        'file_processed_at',
    ];

    protected $casts = [
        'tanggal_invoice' => 'date',
        'jumlah' => 'decimal:2',
        'jatuh_tempo' => 'date',
        'file_size' => 'integer',
        'file_processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['pengadaan_id', 'no_invoice', 'tanggal_invoice', 'jumlah', 'jatuh_tempo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('invoice');
    }

    public function pengadaan(): BelongsTo
    {
        return $this->belongsTo(Pengadaan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->orderBy('tanggal_bayar', 'desc');
    }

    public function isCompleted(): bool
    {
        return $this->file_status === 'completed';
    }

    public function isProcessing(): bool
    {
        return in_array($this->file_status, ['pending', 'processing'], true);
    }

    public function isFailed(): bool
    {
        return $this->file_status === 'failed';
    }

    public function hasFile(): bool
    {
        return ! empty($this->file_status);
    }

    /**
     * Get FileStatus enum from file_status string (null if no file).
     */
    public function getFileStatusEnumAttribute(): ?FileStatus
    {
        return $this->file_status
            ? FileStatus::tryFrom($this->file_status)
            : null;
    }

    /**
     * Total nilai payment yang sudah di-approve (hanya approved yang dihitung
     * sebagai "sudah dibayar" — pending/rejected tidak mengurangi outstanding).
     */
    public function getTotalDibayarAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments
                ->where('status_approval', PaymentApprovalStatus::Approved)
                ->sum('jumlah_bayar');
        }

        return (float) $this->payments()
            ->where('status_approval', PaymentApprovalStatus::Approved)
            ->sum('jumlah_bayar');
    }

    /**
     * Status pembayaran derived (bukan kolom mentah).
     */
    public function getStatusPembayaranAttribute(): StatusPembayaranPengadaan
    {
        $totalDibayar = $this->total_dibayar;

        if ($totalDibayar <= 0) {
            return StatusPembayaranPengadaan::BelumDibayar;
        }

        if ($totalDibayar >= (float) $this->jumlah) {
            return StatusPembayaranPengadaan::Lunas;
        }

        return StatusPembayaranPengadaan::Sebagian;
    }
}
