<?php

namespace App\Models;

use App\Enums\UserApprovalStatus;
use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'pending_email',
        'cabang_id',
        'phone',
        'position',
        'is_active',
        'approval_status',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'approval_status' => UserApprovalStatus::class,
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    // Relationships
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->role($role);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', UserApprovalStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', UserApprovalStatus::Approved);
    }

    public function scopeRejected($query)
    {
        return $query->where('approval_status', UserApprovalStatus::Rejected);
    }

    /**
     * Terapkan scoping cabang ke query builder MODEL LAIN (Pengadaan, dst),
     * KECUALI user punya akses lintas-cabang (permission `access_all_cabang`).
     * Bukan Eloquent local scope (tidak diawali "scope" agar tidak tertukar) —
     * dipanggil manual: $user->applyCabangScope($query).
     * Single source of truth untuk data-scoping COE vs Cabang di seluruh Service/Export.
     */
    public function applyCabangScope($query, string $column = 'cabang_id')
    {
        if ($this->hasGlobalCabangAccess()) {
            return $query;
        }

        return $query->where($column, $this->cabang_id);
    }

    // Accessors
    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole(['super admin', 'admin pusat']);
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->approval_status === UserApprovalStatus::Pending;
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->approval_status === UserApprovalStatus::Approved;
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->approval_status === UserApprovalStatus::Rejected;
    }

    /**
     * Apakah user ini punya akses lintas-cabang (COE/Pusat)?
     * SELALU lewat permission `access_all_cabang`, JANGAN cek nama role.
     */
    public function hasGlobalCabangAccess(): bool
    {
        return $this->can('access_all_cabang');
    }

    public function getFullNameAttribute(): string
    {
        return $this->name.($this->position ? " ({$this->position})" : '');
    }

    /**
     * Get the email address that should be used for verification.
     */
    public function getEmailForVerification()
    {
        // Use pending_email if exists, otherwise use current email
        return $this->pending_email ?? $this->email;
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        // Determine which email to send to
        $emailTo = $this->pending_email ?? $this->email;

        // Send notification directly to the specific email
        \Illuminate\Support\Facades\Notification::route('mail', $emailTo)
            ->notify(new CustomVerifyEmail($this));
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        // Determine which email to send to
        $emailTo = $this->pending_email ?? $this->email;

        // Send notification directly to the specific email
        \Illuminate\Support\Facades\Notification::route('mail', $emailTo)
            ->notify(new CustomResetPassword($token, $this));
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
