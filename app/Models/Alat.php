<?php

namespace App\Models;

use App\Enums\AlatKondisi;
use App\Enums\AlatReviewStatus;
use App\Enums\AlatStatusKalibrasi;
use App\Enums\AlatStatusKepemilikan;
use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Alat extends Model
{
    use HasEncryptedRouteKey, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'merk_type',
        'serial_number',
        'kode_inventaris',
        'description',
        'cabang_id',
        'lokasi',
        'kondisi',
        'status_kepemilikan',
        'is_active',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'approval_note',
    ];

    protected $casts = [
        'kondisi' => AlatKondisi::class,
        'status_kepemilikan' => AlatStatusKepemilikan::class,
        'review_status' => AlatReviewStatus::class,
        'is_active' => 'boolean',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'code', 'name', 'merk_type', 'serial_number', 'kode_inventaris',
                'cabang_id', 'lokasi', 'kondisi',
                'status_kepemilikan', 'is_active', 'review_status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('alat');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(AlatEvidence::class);
    }

    public function kalibrasis(): HasMany
    {
        return $this->hasMany(AlatKalibrasi::class)->orderBy('tanggal_kalibrasi', 'desc');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function logBookPeminjaman(): HasMany
    {
        return $this->hasMany(LogBookPeminjaman::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('review_status', AlatReviewStatus::Approved);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where('review_status', AlatReviewStatus::Approved);
    }

    /**
     * Get latest kalibrasi record (eager-loaded via with('kalibrasis') for performance).
     */
    public function getLatestKalibrasiAttribute(): ?AlatKalibrasi
    {
        if ($this->relationLoaded('kalibrasis')) {
            return $this->kalibrasis->first();
        }

        return $this->kalibrasis()->first();
    }

    /**
     * Derived status kalibrasi from latest kalibrasi record.
     * - Tidak ada kalibrasi record → TidakPerlu
     * - Latest berikutnya < today → Expired
     * - Latest berikutnya ≤ 30 days → Pending (jatuh tempo)
     * - Otherwise → Terkalibrasi
     */
    public function getStatusKalibrasiDerivedAttribute(): AlatStatusKalibrasi
    {
        $latest = $this->latest_kalibrasi;

        if (! $latest || ! $latest->tanggal_kalibrasi_berikutnya) {
            return AlatStatusKalibrasi::TidakPerlu;
        }

        if ($latest->tanggal_kalibrasi_berikutnya->isPast()) {
            return AlatStatusKalibrasi::Expired;
        }

        if (abs($latest->tanggal_kalibrasi_berikutnya->diffInDays(now())) <= 30) {
            return AlatStatusKalibrasi::Pending;
        }

        return AlatStatusKalibrasi::Terkalibrasi;
    }

    public function getCalibrationExpiredAttribute(): bool
    {
        $latest = $this->latest_kalibrasi;

        if (! $latest || ! $latest->tanggal_kalibrasi_berikutnya) {
            return false;
        }

        return $latest->tanggal_kalibrasi_berikutnya->isPast();
    }

    public function getCalibrationExpiringSoonAttribute(): bool
    {
        $latest = $this->latest_kalibrasi;

        if (! $latest || ! $latest->tanggal_kalibrasi_berikutnya) {
            return false;
        }

        return abs($latest->tanggal_kalibrasi_berikutnya->diffInDays(now())) <= 30
            && ! $this->calibration_expired;
    }

    public function isPendingReview(): bool
    {
        return $this->review_status === AlatReviewStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->review_status === AlatReviewStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->review_status === AlatReviewStatus::Rejected;
    }

    public function isCurrentlyBorrowed(): bool
    {
        return $this->logBookPeminjaman()
            ->whereIn('status', [LogBookStatus::Borrowed, LogBookStatus::Overdue])
            ->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
