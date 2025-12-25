<?php

namespace App\Providers;

use App\Models\Review;
use App\Observers\ReviewObserver;
use Illuminate\Support\Facades\App;
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
        Review::observe(ReviewObserver::class);
        if (Auth::check()) {
            App::setLocale(Auth::user()->language ?? 'ar');
        }
    }
}
