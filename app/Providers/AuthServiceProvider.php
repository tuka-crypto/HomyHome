<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Apartment;
use App\Models\Booking;
use App\Policies\ApartmentPolicy;
use App\Policies\BookingPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Apartment::class => ApartmentPolicy::class,
        Booking::class=>BookingPolicy::class
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}