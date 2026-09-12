<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengadaanEvidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengadaan_id',
        'name',
        'file_path',
        'file_name',
        'file_size',
        'file_status',
        'file_error',
        'file_processed_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'file_processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pengadaan(): BelongsTo
    {
        return $this->belongsTo(Pengadaan::class);
    }

    public function isCompleted(): bool
    {
        return $this->file_status === 'completed';
    }

    public function isProcessing(): bool
    {
        return in_array($this->file_status, ['pending', 'processing'], true);
    }
}
