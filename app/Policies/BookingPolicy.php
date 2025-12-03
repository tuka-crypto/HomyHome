<?php

namespace App\Policies;
use App\Models\User;
use App\Models\Booking;
class BookingPolicy
{
    /**
     * المستأجر يقدر يعمل حجز جديد
     */
    public function create(User $user): bool
    {
        return $user->isTenant();
    }

    /**
     * المستأجر يقدر يعدل أو يلغي حجوزاته فقط
     */
    public function update(User $user, Booking $booking): bool
    {
        return $user->isTenant() && $user->id === $booking->tenant_id;
    }
    /**
     * المستأجر يقدر يشوف حجوزاته فقط
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->isTenant() && $user->id === $booking->tenant_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->isTenant();
    }

    /**
     * المالك يقدر يوافق أو يرفض الحجوزات الخاصة بشققه فقط
     */
    public function approve(User $user, Booking $booking): bool
    {
        return $user->isOwner() && $user->id === $booking->apartment->owner_id;
    }

    public function reject(User $user, Booking $booking): bool
    {
        return $user->isOwner() && $user->id === $booking->apartment->owner_id;
    }
}