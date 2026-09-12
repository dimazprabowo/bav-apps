<?php

namespace App\Models;

use App\Enums\FileStatus;
use App\Enums\PaymentApprovalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InvoicePayment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'invoice_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'metode_bayar',
        'status_approval',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'catatan',
        'file_path',
        'file_name',
        'file_size',
        'file_status',
        'file_error',
        'file_processed_at',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah_bayar' => 'decimal:2',
        'status_approval' => PaymentApprovalStatus::class,
        'approved_at' => 'datetime',
        'file_size' => 'integer',
        'file_processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['invoice_id', 'tanggal_bayar', 'jumlah_bayar', 'metode_bayar', 'status_approval', 'rejection_reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('invoice-payment');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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

    public function isPending(): bool
    {
        return $this->status_approval === PaymentApprovalStatus::Pending;
    }
}
