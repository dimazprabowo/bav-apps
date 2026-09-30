<?php

namespace App\Livewire\Traits;

use App\Models\Cabang;
use App\Models\Chat;
use App\Models\KategoriItem;
use App\Models\Klaster;
use App\Models\Notification;
use App\Models\Pengadaan;
use App\Models\Satuan;
use App\Models\SystemConfiguration;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

trait HasMenuItems
{
    public function getMenuItems(): array
    {
        $user = Auth::user();

        // Cache per request — Sidebar and Navigation both call this, avoid double DB hit
        static $cache = [];
        $cacheKey = 'menu_'.$user->id;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        // All checks go through Gate → policies (respects Gate::before super-admin bypass)
        $perms = [
            'dashboard_view' => Gate::allows('viewStats'),
            'cabang_view' => Gate::allows('viewAny', Cabang::class),
            'klaster_view' => Gate::allows('viewAny', Klaster::class),
            'satuan_view' => Gate::allows('viewAny', Satuan::class),
            'kategori_item_view' => Gate::allows('viewAny', KategoriItem::class),
            'vendor_view' => Gate::allows('viewAny', Vendor::class),
            'pengadaan_view' => Gate::allows('viewAny', Pengadaan::class),
            'notifications_view' => Gate::allows('viewAny', Notification::class),
            'notifications_send' => Gate::allows('send', Notification::class),
            'chat_view' => Gate::allows('viewAny', Chat::class),
            'configuration_view' => Gate::allows('viewAny', SystemConfiguration::class),
            'users_view' => Gate::allows('viewAny', User::class),
            'roles_view' => Gate::allows('viewAny', Role::class),
        ];

        $req = request();
        $items = [];

        // Dashboard — all roles
        $items[] = [
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'home',
            'active' => $req->routeIs('dashboard'),
        ];

        // Master Data
        $masterDataChildren = [];
        if ($perms['cabang_view']) {
            $masterDataChildren[] = [
                'name' => 'Cabang',
                'route' => 'master-data.cabangs',
                'active' => $req->routeIs('master-data.cabangs'),
            ];
        }
        if ($perms['klaster_view']) {
            $masterDataChildren[] = [
                'name' => 'Klaster',
                'route' => 'master-data.klasters',
                'active' => $req->routeIs('master-data.klasters'),
            ];
        }
        if ($perms['satuan_view']) {
            $masterDataChildren[] = [
                'name' => 'Satuan',
                'route' => 'master-data.satuans',
                'active' => $req->routeIs('master-data.satuans'),
            ];
        }
        if ($perms['kategori_item_view']) {
            $masterDataChildren[] = [
                'name' => 'Kategori Item',
                'route' => 'master-data.kategori-items',
                'active' => $req->routeIs('master-data.kategori-items'),
            ];
        }
        if ($perms['vendor_view']) {
            $masterDataChildren[] = [
                'name' => 'Vendor',
                'route' => 'master-data.vendors',
                'active' => $req->routeIs('master-data.vendors'),
            ];
        }
        if (! empty($masterDataChildren)) {
            $items[] = [
                'name' => 'Master Data',
                'icon' => 'database',
                'active' => $req->routeIs('master-data.*'),
                'children' => $masterDataChildren,
            ];
        }

        // Pengadaan Aset
        if ($perms['pengadaan_view']) {
            $items[] = [
                'name' => 'Pengadaan Aset',
                'route' => 'pengadaan.index',
                'icon' => 'shopping-cart',
                'active' => $req->routeIs('pengadaan.*'),
            ];
        }

        // Notifikasi
        $canView = $perms['notifications_view'];
        $canSend = $perms['notifications_send'];

        if ($canView || $canSend) {
            $notifChildren = [];
            if ($canView) {
                $notifChildren[] = [
                    'name' => 'Kotak Masuk',
                    'route' => 'notifications.index',
                    'active' => $req->routeIs('notifications.index'),
                ];
            }
            if ($canSend) {
                $notifChildren[] = [
                    'name' => 'Kirim Notifikasi',
                    'route' => 'notifications.send',
                    'active' => $req->routeIs('notifications.send'),
                ];
            }

            if ($canView && $canSend) {
                $items[] = [
                    'name' => 'Notifikasi',
                    'icon' => 'bell',
                    'active' => $req->routeIs('notifications.*'),
                    'children' => $notifChildren,
                ];
            } else {
                $items[] = [
                    'name' => 'Notifikasi',
                    'route' => $canView ? 'notifications.index' : 'notifications.send',
                    'icon' => 'bell',
                    'active' => $req->routeIs('notifications.*'),
                ];
            }
        }

        // Chat
        if ($perms['chat_view']) {
            $items[] = [
                'name' => 'Chat',
                'route' => 'chat.index',
                'icon' => 'chat',
                'active' => $req->routeIs('chat.*'),
            ];
        }

        // Pengaturan
        $settingsChildren = [];
        if ($perms['configuration_view']) {
            $settingsChildren[] = [
                'name' => 'Konfigurasi System',
                'route' => 'settings.system',
                'active' => $req->routeIs('settings.system'),
            ];
        }
        if ($perms['users_view']) {
            $settingsChildren[] = [
                'name' => 'Manajemen User',
                'route' => 'settings.users',
                'active' => $req->routeIs('settings.users'),
            ];
        }
        if ($perms['roles_view']) {
            $settingsChildren[] = [
                'name' => 'Roles & Permissions',
                'route' => 'settings.roles',
                'active' => $req->routeIs('settings.roles'),
            ];
        }
        if (! empty($settingsChildren)) {
            $items[] = [
                'name' => 'Pengaturan',
                'icon' => 'cog',
                'active' => $req->routeIs('settings.*'),
                'children' => $settingsChildren,
            ];
        }

        $result = array_filter($items);
        $cache[$cacheKey] = $result;

        return $result;
    }
}
