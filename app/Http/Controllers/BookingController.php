<?php
namespace App\Http\Controllers;
use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Apartment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Carbon\Carbon;
class BookingController extends Controller
{
    private function checkingDate($apartmentId, $startDate, $endDate, $excludeBookingId = null)
    {
        return Booking::where('apartment_id', $apartmentId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($excludeBookingId, function ($query) use ($excludeBookingId) {
                $query->where('id', '!=', $excludeBookingId);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                });
            })
            ->exists();
    }
    public function myBookings(BookingRequest $request)
    {
        $bookings = Booking::with('apartment')
            ->where('tenant_id', $request->user()->id)
            ->orderBy('start_date', 'desc')
            ->get();
        return response()->json([
            'data'   => $bookings,
            'status' => 'success',
        ]);
    }
    public function store(BookingRequest $request)
    {
        Gate::authorize('create', Booking::class);
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'start_date'   => 'required|date|after_or_equal:today',
            'end_date'     => 'required|date|after:start_date',
            'guest_count'  => 'required|integer|min:1',
        ]);
        $apartment = Apartment::findOrFail($validated['apartment_id']);
        if ($apartment->status !== 'approved' || !$apartment->is_available) {
            return response()->json(['status' => 'error'], 403);
        }
        if ($this->checkingDate($apartment->id, $validated['start_date'], $validated['end_date'])) {
            return response()->json(['status' => 'error'], 409);
        }
        $days = (new \DateTime($validated['end_date']))->diff(new \DateTime($validated['start_date']))->days;
        $totalPrice = $apartment->price * $days;
        $booking = Booking::create([
            'apartment_id'   => $apartment->id,
            'tenant_id'      => $request->user()->id,
            'start_date'     => $validated['start_date'],
            'end_date'       => $validated['end_date'],
            'guest_count'    => $validated['guest_count'],
            'total_price'    => $totalPrice,
            'status'         => 'pending',
            'owner_approved' => false,
            'booking_number' => Str::uuid(),
        ]);
        return response()->json([
            'data'   => $booking->load('apartment'),
            'status' => 'success',
        ], 201);
    }
    public function update(BookingRequest $request, Booking $booking)
    {
        Gate::authorize('update', $booking);
        $validated = $request->validate([
            'start_date'  => 'sometimes|date|after_or_equal:today',
            'end_date'    => 'sometimes|date|after:start_date',
            'guest_count' => 'sometimes|integer|min:1',
        ]);
        if (isset($validated['start_date'], $validated['end_date']) &&
            $this->checkingDate($booking->apartment_id, $validated['start_date'], $validated['end_date'], $booking->id)) {
            return response()->json(['status' => 'error'], 409);
        }
        $booking->update(array_merge($validated, [
            'status'         => 'pending',
            'owner_approved' => false,
        ]));
        return response()->json([
            'data'   => $booking->load('apartment'),
            'status' => 'success',
        ]);
    }
    public function cancel(BookingRequest $request, Booking $booking)
    {
        Gate::authorize('cancel', $booking);
        if ($booking->status === 'confirmed' && Carbon::now()->greaterThanOrEqualTo($booking->start_date)) {
            return response()->json(['status' => 'error'], 400);
        }
        $booking->update([
            'status'         => 'canceled',
            'owner_approved' => false,
        ]);
        return response()->json([
            'data'   => $booking->load('apartment'),
            'status' => 'success',
        ]);
    }
    public function approve(BookingRequest $request, Booking $booking)
    {
        Gate::authorize('approve', $booking);
        if ($this->checkingDate($booking->apartment_id, $booking->start_date, $booking->end_date, $booking->id)) {
            return response()->json(['status' => 'error'], 409);
        }
        $booking->update([
            'owner_approved' => true,
            'status'         => 'confirmed',
        ]);
        return response()->json([
            'data'   => $booking->load('apartment'),
            'status' => 'success',
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
            'data'   => $booking->load('apartment'),
            'status' => 'success',
        ]);
    }
    public function pendingBookingsForOwner(BookingRequest $request)
    {
        $bookings = Booking::with('apartment', 'tenant')
            ->whereHas('apartment', function ($query) use ($request) {
                $query->where('owner_id', $request->user()->id);
            })
            ->where('status', 'pending')
            ->orderBy('start_date', 'asc')
            ->get();
        return response()->json([
            'data'   => $bookings,
            'status' => 'success',
        ]);
    }
}