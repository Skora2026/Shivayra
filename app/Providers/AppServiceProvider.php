<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        // Global front-end data must be attached when a view is actually
        // rendered — not at provider boot, which runs before RefreshDatabase
        // migrates in tests (that made $settings undefined in the layout).
        // Auth pages are standalone and also consume $settings.
        View::composer(['front.*', 'auth.*', 'admin.login', 'admin.layouts.*', 'emails.*'], function ($view) {
            if (! Schema::hasTable('settings')) {
                return;
            }

            // Once per request, not once per partial: front.* matches many
            // included partials on a single page render.
            $settings = app()->bound('composer.settings')
                ? app('composer.settings')
                : app()->instance('composer.settings', Setting::first());

            $headerCategories = app()->bound('composer.headerCategories')
                ? app('composer.headerCategories')
                : app()->instance('composer.headerCategories', Category::with(['subCategories' => function ($q) {
                    $q->where('status', 'active');
                }])
                    ->where('status', 'active')
                    ->get());

            $view->with('settings', $settings);
            $view->with('headerCategories', $headerCategories);
        });
    }
}
