<?php

namespace App\Livewire\Pages;

use App\Models\User;
use App\Services\PengadaanService;
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

            // Pengadaan Aset stats (biaya, invoice, pembayaran), cabang-scoped via service.
            if (Gate::allows('pengadaan_view')) {
                $pengadaanService = app(PengadaanService::class);
                $data['pengadaanStats'] = $pengadaanService->getDashboardStats();
                $data['pengadaanPerVendor'] = $pengadaanService->getSpendPerVendor();

                if (Gate::allows('access_all_cabang')) {
                    $data['pengadaanPerCabang'] = $pengadaanService->getSpendPerCabang();
                }
            }
        }

        return view('livewire.pages.dashboard', $data);
    }
}
