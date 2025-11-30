<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Apartment;
use App\Policies\ApartmentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Apartment::class => ApartmentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}