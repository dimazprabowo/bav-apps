<?php

namespace App\Models;

use App\Enums\AlatKondisi;
use App\Enums\LogBookStatus;
use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LogBookPeminjaman extends Model
{
    use HasEncryptedRouteKey, HasFactory, LogsActivity;

    protected $table = 'log_book_peminjaman';

    protected $fillable = [
        'alat_id',
        'peminjam_id',
        'cabang_id',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        'tanggal_kembali_aktual',
        'deskripsi_pekerjaan',
        'status',
        'kondisi_pinjam',
        'kondisi_kembali',
        'catatan',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'cancellation_reason',
        'created_by',
    ];

    protected $casts = [
        'status' => LogBookStatus::class,
        'kondisi_pinjam' => AlatKondisi::class,
        'kondisi_kembali' => AlatKondisi::class,
        'tanggal_pinjam' => 'date',
        'tanggal_kembali_rencana' => 'date',
        'tanggal_kembali_aktual' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'alat_id', 'peminjam_id', 'cabang_id',
                'tanggal_pinjam', 'tanggal_kembali_rencana', 'tanggal_kembali_aktual',
                'deskripsi_pekerjaan',
                'status', 'kondisi_pinjam', 'kondisi_kembali', 'catatan',
                'approved_by', 'approved_at', 'rejection_reason', 'cancellation_reason',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('logbook');
    }

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class);
    }

    public function peminjam(): BelongsTo
    {
        return $this->belongsTo(User::class, 'peminjam_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeRequested($query)
    {
        return $query->where('status', LogBookStatus::Requested);
    }

    public function scopeBorrowed($query)
    {
        return $query->where('status', LogBookStatus::Borrowed);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', LogBookStatus::Overdue);
    }

    public function getIsOverdueAttribute(): bool
    {
        return in_array($this->status, [LogBookStatus::Borrowed, LogBookStatus::Overdue])
            && $this->tanggal_kembali_rencana->isPast()
            && ! $this->tanggal_kembali_aktual;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
