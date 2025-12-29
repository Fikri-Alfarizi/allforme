<?php

namespace App\Providers;

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
        // Share notifications with all views (or specifically the header)
        \Illuminate\Support\Facades\View::composer('layouts.header', function ($view) {
            if (auth()->check()) {
                $notifications = auth()->user()->unreadNotifications;
                $view->with('unreadNotifications', $notifications);
            } else {
                $view->with('unreadNotifications', collect([]));
            }
        });
    }
}
