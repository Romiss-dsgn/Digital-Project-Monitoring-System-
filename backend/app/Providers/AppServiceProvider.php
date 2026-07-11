<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Passport::enablePasswordGrant();

        Gate::define('engineering-plans.upload', function ($user) {
            return in_array($user->role?->name, [
                'Engineer - Planning',
                'System Administrator',
            ]);
        });

        Gate::define('engineering-plans.review', function ($user) {
            return in_array($user->role?->name, [
                'Engineer - Planning',
                'Engineer - Supervision',
                'System Administrator',
            ]);
        });

        Gate::define('engineering-plans.archive', function ($user) {
            return in_array($user->role?->name, [
                'Engineer - Planning',
                'System Administrator',
            ]);
        });
    }
}