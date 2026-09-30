<?php

namespace App\Providers;

use App\Models\Cabang;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\KategoriItem;
use App\Models\Klaster;
use App\Models\Notification;
use App\Models\Pengadaan;
use App\Models\Satuan;
use App\Models\SystemConfiguration;
use App\Models\User;
use App\Models\Vendor;
use App\Policies\CabangPolicy;
use App\Policies\ChatPolicy;
use App\Policies\DashboardPolicy;
use App\Policies\KategoriItemPolicy;
use App\Policies\KlasterPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\PengadaanPolicy;
use App\Policies\RolePolicy;
use App\Policies\SatuanPolicy;
use App\Policies\SystemConfigurationPolicy;
use App\Policies\UserPolicy;
use App\Policies\VendorPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS when behind reverse proxy (Cloudflare Tunnel, load balancer, etc.)
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        // Register Policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Cabang::class, CabangPolicy::class);
        Gate::policy(Klaster::class, KlasterPolicy::class);
        Gate::policy(SystemConfiguration::class, SystemConfigurationPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
        Gate::policy(Chat::class, ChatPolicy::class);
        Gate::policy(ChatMessage::class, ChatPolicy::class);
        Gate::policy(Vendor::class, VendorPolicy::class);
        Gate::policy(Satuan::class, SatuanPolicy::class);
        Gate::policy(KategoriItem::class, KategoriItemPolicy::class);
        Gate::policy(Pengadaan::class, PengadaanPolicy::class);

        // Dashboard policy — bound to a string key (no Eloquent model)
        Gate::define('viewStats', [DashboardPolicy::class, 'viewStats']);

        // Super admin bypasses all permission checks
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super admin') ? true : null;
        });

        // Override Laravel config with database system configurations
        $this->applySystemConfigurations();
    }

    /**
     * Apply system configurations from database to Laravel config.
     */
    private function applySystemConfigurations(): void
    {
        try {
            if (! Schema::hasTable('system_configurations')) {
                return;
            }

            // Map of system_configuration keys to Laravel config keys
            $configMap = [
                'app.name' => 'app.name',
                'app.timezone' => 'app.timezone',
            ];

            foreach ($configMap as $dbKey => $laravelKey) {
                $value = SystemConfiguration::get($dbKey);
                if ($value !== null) {
                    config([$laravelKey => $value]);
                }
            }

            // Apply timezone if set
            $timezone = config('app.timezone');
            if ($timezone) {
                date_default_timezone_set($timezone);
            }
        } catch (\Exception $e) {
            // Silently fail if database is not available (e.g. during migrations)
        }
    }
}
