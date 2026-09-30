<?php

namespace App\Models;

use App\Enums\KlasterStatus;
use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Klaster extends Model
{
    use HasEncryptedRouteKey, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => KlasterStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'name', 'description', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('klaster');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', KlasterStatus::Aktif);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === KlasterStatus::Aktif;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
