<?php

namespace App\Providers;

use App\Models\Shared\User;
use App\Support\PermissionRegistry;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('admin', fn (User $user): bool => PermissionRegistry::isAdmin($user));
        Gate::define('super-admin', fn (User $user): bool => PermissionRegistry::isSuperAdmin($user));
        Gate::define('view-profit', fn (User $user): bool => PermissionRegistry::canViewProfit($user));
        Gate::define('view-activity-log', fn (User $user): bool => PermissionRegistry::canViewActivityLog($user));

        foreach (PermissionRegistry::allKeys() as $key) {
            Gate::define("perm.{$key}", fn (User $user): bool => PermissionRegistry::allows($user, $key));
        }
    }
}
