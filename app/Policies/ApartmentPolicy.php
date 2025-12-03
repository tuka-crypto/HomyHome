<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Apartment;

class ApartmentPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }
    public function view(?User $user, Apartment $apartment): bool
    {
        return true;
    }
    public function create(User $user): bool
    {
        return $user->isOwner();
    }
    public function update(User $user, Apartment $apartment): bool
    {
        return $user->isOwner();
    }
    public function delete(User $user, Apartment $apartment): bool
    {
        return $user->isOwner();
    }
}