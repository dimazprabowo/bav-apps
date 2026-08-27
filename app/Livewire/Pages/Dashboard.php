<?php

namespace App\Livewire\Pages;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $canViewStats = Gate::allows('viewStats');

        $data = [
            'authUser' => $user,
            'authUserRole' => $user->getRoleNames()->join(', ') ?: 'User',
            'canViewStats' => $canViewStats,
            'appJoinedAt' => $user->created_at,
        ];

        if ($canViewStats) {
            $data['totalUsers'] = User::count();
            $data['totalRoles'] = Role::count();
        }

        return view('livewire.pages.dashboard', $data);
    }
}
