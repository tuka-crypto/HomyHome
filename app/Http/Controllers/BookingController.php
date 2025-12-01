<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use Carbon\Carbon;
class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = auth()->id();
        $today = Carbon::today();

        // الحجوزات الجديدة (pending)
        $newBookings = Booking::where('tenant_id', $userId)
            ->where('status', 'pending')
            ->orderBy('start_date', 'desc')
            ->get();

        // الحجوزات الحالية (confirmed والوقت الحالي بين start و end)
        $currentBookings = Booking::where('tenant_id', $userId)
            ->where('status', 'confirmed')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->orderBy('start_date', 'desc')
            ->get();

        // الحجوزات القديمة (منتهية أو ملغاة)
        $oldBookings = Booking::where('tenant_id', $userId)
            ->where(function ($q) use ($today) {
                $q->where('end_date', '<', $today)
                ->orWhere('status', 'canceled');
            })
            ->orderBy('end_date', 'desc')
            ->get();

        return response()->json([
            'new' => $newBookings,
            'current' => $currentBookings,
            'old' => $oldBookings,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }
}
