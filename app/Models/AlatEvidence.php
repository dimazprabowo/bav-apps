<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlatEvidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'alat_id',
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
}
