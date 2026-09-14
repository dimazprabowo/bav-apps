<?php

namespace App\Services;

use App\Enums\UserApprovalStatus;
use App\Helpers\ConfigHelper;
use App\Models\User;
use App\Traits\HasDynamicLike;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    use HasDynamicLike;

    public function getFilteredUsers(
        ?string $search = null,
        ?string $roleFilter = null,
        ?string $isActive = null,
        ?string $approvalStatus = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = User::with(['roles', 'cabang']);

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->where('name', $operator, "%{$search}%")
                    ->orWhere('email', $operator, "%{$search}%")
                    ->orWhere('phone', $operator, "%{$search}%")
                    ->orWhere('position', $operator, "%{$search}%");
            });
        }

        if ($roleFilter) {
            $query->role($roleFilter);
        }

        if ($isActive !== null && $isActive !== '') {
            $query->where('is_active', $isActive === '1');
        }

        if ($approvalStatus !== null && $approvalStatus !== '') {
            $query->where('approval_status', $approvalStatus);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function create(array $data, array $roles): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'cabang_id' => $data['cabang_id'] ?? null,
            'phone' => $data['phone'] ?? null,
            'position' => $data['position'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'approval_status' => UserApprovalStatus::Approved,
            'email_verified_at' => now(),
        ]);

        $user->syncRoles($roles);

        return $user;
    }

    public function update(User $user, array $data, array $roles): User
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'cabang_id' => $data['cabang_id'] ?? null,
            'phone' => $data['phone'] ?? null,
            'position' => $data['position'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
        $user->syncRoles($roles);

        if (! $user->is_active) {
            $this->invalidateSessions($user);
        }

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function toggleActive(User $user): User
    {
        $user->update(['is_active' => ! $user->is_active]);

        if (! $user->is_active) {
            $this->invalidateSessions($user);
        }

        return $user;
    }

    public function resetPassword(User $user, string $newPassword): void
    {
        $user->update(['password' => Hash::make($newPassword)]);
    }

    public function approveUser(User $user, User $approver): User
    {
        if ($user->approval_status !== UserApprovalStatus::Pending) {
            throw new \DomainException('Hanya user dengan status menunggu approval yang dapat disetujui.');
        }

        return DB::transaction(function () use ($user, $approver) {
            $user->update([
                'approval_status' => UserApprovalStatus::Approved,
                'is_active' => true,
                'approved_at' => now(),
                'approved_by' => $approver->id,
                'rejected_at' => null,
                'rejected_by' => null,
                'rejection_reason' => null,
            ]);

            // Assign default role jika user belum punya role
            if ($user->roles->isEmpty()) {
                $user->assignRole(ConfigHelper::getDefaultRegistrationRole());
            }

            // Kirim email verifikasi setelah approval (link verifikasi butuh login, jadi wajib tunggu akun aktif)
            if (! $user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();
            }

            return $user;
        });
    }

    public function rejectUser(User $user, User $rejecter, string $reason): User
    {
        if ($user->approval_status !== UserApprovalStatus::Pending) {
            throw new \DomainException('Hanya user dengan status menunggu approval yang dapat ditolak.');
        }

        return DB::transaction(function () use ($user, $rejecter, $reason) {
            $user->update([
                'approval_status' => UserApprovalStatus::Rejected,
                'is_active' => false,
                'rejected_at' => now(),
                'rejected_by' => $rejecter->id,
                'rejection_reason' => $reason,
                'approved_at' => null,
                'approved_by' => null,
            ]);

            $this->invalidateSessions($user);

            return $user;
        });
    }

    public function getPendingApprovalCount(): int
    {
        return User::pendingApproval()->count();
    }

    public function isSelf(int $userId): bool
    {
        return $userId === (int) auth()->id();
    }

    private function invalidateSessions(User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
    }
}
