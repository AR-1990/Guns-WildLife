<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

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
        RedirectIfAuthenticated::redirectUsing(function ($request, $guards = null) {
            $user = Auth::guard(empty($guards) ? null : $guards[0])->user();
            if (! $user) {
                return '/';
            }

            return route($user->dashboardRouteName());
        });
    }
}
