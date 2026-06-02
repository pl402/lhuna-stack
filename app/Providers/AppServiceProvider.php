<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        Inertia::share("logo", config("app.logo"));
        Inertia::share("logoNav", config("app.logoNav"));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(database_path('migrations/designer'));

        // Reset designer files automatically when running migrate:fresh
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Console\Events\CommandStarting::class,
            function (\Illuminate\Console\Events\CommandStarting $event) {
                if ($event->command === 'migrate:fresh') {
                    \Illuminate\Support\Facades\Artisan::call('designer:reset');
                }
            }
        );
    }
}
