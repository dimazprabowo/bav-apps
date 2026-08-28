<?php

namespace App\Livewire\Pages;

use App\Models\User;
use App\Services\AlatService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $canViewStats = Gate::allows('dashboard_view');

        $data = [
            'authUser' => $user,
            'authUserRole' => $user->getRoleNames()->join(', ') ?: 'User',
            'canViewStats' => $canViewStats,
            'appJoinedAt' => $user->created_at,
        ];

        if ($canViewStats) {
            // Only query counts the user is permitted to see (avoid information leak + unnecessary queries).
            if (Gate::allows('users_view')) {
                $data['totalUsers'] = User::count();
            }
            if (Gate::allows('roles_view')) {
                $data['totalRoles'] = Role::count();
            }

            // Equipment monitoring stats (kondisi + kalibrasi), cabang-scoped via service.
            if (Gate::allows('alat_view')) {
                $data['alatStats'] = app(AlatService::class)->getDashboardStats();
            }
        }

        return view('livewire.pages.dashboard', $data);
    }
}
