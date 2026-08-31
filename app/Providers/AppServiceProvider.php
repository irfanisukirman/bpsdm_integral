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
        \Illuminate\Support\Facades\View::composer('layouts.navbar', function ($view) {
            $notifications = auth()->check()
                ? app(\App\Services\NotificationCenter::class)->forUser(auth()->user())
                : collect();

            $view->with('navbarNotifications', $notifications);
        });

        
        \Illuminate\Support\Facades\Gate::define('superadmin-only', function ($user) {
            return $user->role === 'superadmin';
        });

        \Illuminate\Support\Facades\Gate::define('isParticipant', function ($user) {
            return $user->role === 'participant';
        });

        \Illuminate\Pagination\Paginator::useBootstrapFive(); 
    }
}
