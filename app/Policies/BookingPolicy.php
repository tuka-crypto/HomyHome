<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Booking;

class BookingPolicy
{
    // only tenant can create a booking
    public function create(User $user): bool
    {
        return $user->isTenant();
    }
    // only tenant can update a booking
    public function update(User $user, Booking $booking): bool
    {
        return $user->isTenant() && $user->id === $booking->tenant_id;
    }
    // only tenant can delete a booking befor the booking start
    public function cancel(User $user, Booking $booking): bool
    {
        return $user->isTenant() && $user->id === $booking->tenant_id;
    }
    // only tenant can view his one booking
    public function view(User $user, Booking $booking): bool
    {
        return $user->isTenant() && $user->id === $booking->tenant_id;
    }
     // only tenant can view his all booking
    public function viewAny(User $user): bool
    {
        return $user->isTenant();
    }
    // the owner of apartment approve to book the apartment
    public function approve(User $user, Booking $booking): bool
    {
        return $user->isOwner() && $user->id === $booking->apartment->owner_id;
    }
    // the owner of apartment reject to book the apartment
    public function reject(User $user, Booking $booking): bool
    {
        return $user->isOwner() && $user->id === $booking->apartment->owner_id;
    }
}