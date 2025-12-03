<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Apartment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function myBookings(BookingRequest $request)
    {
        $bookings = Booking::with('apartment')
            ->where('tenant_id', $request->user()->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'data'    => $bookings,
            'status'  => 'success',
            'message' => 'Bookings retrieved successfully.',
        ]);
    }

    public function store(BookingRequest $request)
{
    Gate::authorize('create',Booking::class);
    $validated = $request->validate([
        'apartment_id' => 'required|exists:apartments,id',
        'start_date'   => 'required|date|after_or_equal:today',
        'end_date'     => 'required|date|after:start_date',
        'guest_count'  => 'required|integer|min:1',
    ]);

    $apartment = Apartment::findOrFail($validated['apartment_id']);
    $days = (new \DateTime($validated['end_date']))->diff(new \DateTime($validated['start_date']))->days;
    $totalPrice = $apartment->price * $days;
    $booking = Booking::create([
        'apartment_id'   => $apartment->id,
        'tenant_id'      => $request->user()->id,
        'start_date'     => $validated['start_date'],
        'end_date'       => $validated['end_date'],
        'guest_count'    => $validated['guest_count'],
        'total_price'    => $totalPrice,
        'status'         => 'pending',       // يبدأ كـ pending
        'owner_approved' => false,           // المالك لسه ما وافق
        'booking_number' => Str::uuid(),
    ]);
    return response()->json([
        'data'    => $booking->load('apartment'),
        'status'  => 'success',
        'message' => 'Booking request submitted. Waiting for owner approval.',
    ], 201);
}

    public function update(BookingRequest $request, Booking $booking)
{
    Gate::authorize('update',$booking);
    $validated = $request->validate([
        'start_date'  => 'sometimes|date|after_or_equal:today',
        'end_date'    => 'sometimes|date|after:start_date',
        'guest_count' => 'sometimes|integer|min:1',
    ]);

    $booking->update(array_merge($validated, [
        'status'         => 'pending',   // يرجع Pending
        'owner_approved' => false,       // يحتاج موافقة جديدة
    ]));

    return response()->json([
        'data'    => $booking->load('apartment'),
        'status'  => 'success',
        'message' => 'Booking update submitted. Waiting for owner approval.',
    ]);
}

    public function approve(BookingRequest $request, Booking $booking)
    {
        Gate::authorize('approve', $booking);

        $booking->update([
            'owner_approved' => true,
            'status'         => 'confirmed',
        ]);

        return response()->json([
            'data'    => $booking->load('apartment'),
            'status'  => 'success',
            'message' => 'Booking approved by owner.',
        ]);
    }
    public function reject(BookingRequest $request, Booking $booking)
    {
        Gate::authorize('reject', $booking);

        $booking->update([
            'owner_approved' => false,
            'status'         => 'canceled',
        ]);

        return response()->json([
            'data'    => $booking->load('apartment'),
            'status'  => 'success',
            'message' => 'Booking rejected by owner.',
        ]);
    }
}

