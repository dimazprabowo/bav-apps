<?php

namespace App\Models;

use App\Enums\KalibrasiHasil;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AlatKalibrasi extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'alat_id',
        'tanggal_kalibrasi',
        'tanggal_kalibrasi_berikutnya',
        'vendor',
        'sertifikat_no',
        'hasil',
        'catatan',
        'file_path',
        'file_name',
        'file_size',
        'file_status',
        'file_error',
        'file_processed_at',
    ];

    protected $casts = [
        'tanggal_kalibrasi' => 'date',
        'tanggal_kalibrasi_berikutnya' => 'date',
        'hasil' => KalibrasiHasil::class,
        'file_size' => 'integer',
        'file_processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'alat_id', 'tanggal_kalibrasi', 'tanggal_kalibrasi_berikutnya',
                'vendor', 'sertifikat_no', 'hasil', 'catatan',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('alat-kalibrasi');
    }

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class);
    }

    public function isCompleted(): bool
    {
        return $this->file_status === 'completed';
    }

    public function isProcessing(): bool
    {
        return $this->file_status === 'processing';
    }

    public function isFailed(): bool
    {
        return $this->file_status === 'failed';
    }

    public function hasFile(): bool
    {
        return $this->file_name !== null;
    }

    public function isExpired(): bool
    {
        return $this->tanggal_kalibrasi_berikutnya
            && $this->tanggal_kalibrasi_berikutnya->isPast();
    }

    public function isExpiringSoon(): bool
    {
        if (! $this->tanggal_kalibrasi_berikutnya) {
            return false;
        }

        return abs($this->tanggal_kalibrasi_berikutnya->diffInDays(now())) <= 30
            && ! $this->isExpired();
    }
}
