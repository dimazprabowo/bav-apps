<?php

namespace App\Models;

use App\Enums\VendorStatus;
use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Vendor extends Model
{
    use HasEncryptedRouteKey, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'npwp',
        'status',
    ];

    protected $casts = [
        'status' => VendorStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'name', 'contact_person', 'phone', 'email', 'address', 'npwp', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('vendor');
    }

    public function pengadaans(): HasMany
    {
        return $this->hasMany(Pengadaan::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', VendorStatus::Aktif);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === VendorStatus::Aktif;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
