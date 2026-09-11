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
        \Illuminate\Support\Facades\View::composer(['layouts.navbar', 'layouts.sidebar'], function ($view) {
            $user = auth()->user();
            if (! $user) {
                $view->with(['navbarNotifications' => collect(), 'menuNotificationCounts' => collect()]);
                return;
            }
            $request = request();
            if (! $request->attributes->has('_integral_notifications')) {
                $center = app(\App\Services\NotificationCenter::class);
                $items = $center->forUser($user);
                $request->attributes->set('_integral_notifications', [$items, $center->menuCounts($items)]);
            }
            [$items, $counts] = $request->attributes->get('_integral_notifications');
            $view->with(['navbarNotifications' => $items, 'menuNotificationCounts' => $counts]);
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
