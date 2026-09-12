<?php

namespace App\Models;

use App\Enums\CabangStatus;
use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Cabang extends Model
{
    use HasEncryptedRouteKey, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'pic_name',
        'pic_phone',
        'status',
    ];

    protected $casts = [
        'status' => CabangStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'name', 'address', 'phone', 'pic_name', 'pic_phone', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('cabang');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', CabangStatus::Active);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === CabangStatus::Active;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
