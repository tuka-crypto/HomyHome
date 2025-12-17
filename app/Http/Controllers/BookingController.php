<?php
namespace App\Http\Controllers;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Apartment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
// checking that the date of booking is right and donnot overlap with other bookings
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
//show the tenant his bookings (previous , canceled, present booking )
    public function myBookings(Request $request)
    {
        $bookings = Booking::with('apartment')
            ->where('tenant_id', $request->user()->id)
            ->orderBy('start_date', 'desc')
            ->get();
        return BookingResource::collection($bookings);
    }
// the tenant can make a booking
    public function store(BookingRequest $request)
    {
        Gate::authorize('create', Booking::class);
        $apartment = Apartment::findOrFail($request['apartment_id']);
        if ($apartment->status !== 'approved' || !$apartment->is_available) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Apartment not available for booking'
            ], 403);
        }
        if ($this->checkingDate($apartment->id, $request['start_date'], $request['end_date'])) {
            return response()->json([
                'status' => 'Error',
                'message'=>'Booking dates overlap with existing bookings'
            ], 409);
        }
        $days = (new \DateTime($request['end_date']))->diff(new \DateTime($request['start_date']))->days;
        if ($days < 1) {
        return response()->json([
        'status' => 'Error',
        'message' => 'Booking must be at least one night'
        ], 422);
        }
        $totalPrice = $apartment->price * $days;
        $booking = Booking::create([
            'apartment_id'   => $apartment->id,
            'tenant_id'      => $request->user()->id,
            'start_date'     => $request['start_date'],
            'end_date'       => $request['end_date'],
            'guest_count'    => $request['guest_count'],
            'total_price'    => $totalPrice,
            'status'         => 'pending',
            'owner_approved' => false,
            'booking_number' => Str::uuid(),
        ]);
        return new BookingResource($booking->load('apartment'));
    }
//update his booking (start_date,end_date,guest_count)
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        Gate::authorize('update', $booking);
        if (isset($request['start_date'], $request['end_date']) &&
            $this->checkingDate($booking->apartment_id, $request['start_date'], $request['end_date'], $booking->id)) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Booking dates overlap with existing bookings'
                ], 409);
        }
        $data = $request->only([
        'start_date',
        'end_date',
        'guest_count'
    ]);
    $data['status'] = 'pending';
    $data['owner_approved'] = false;
    $booking->update($data);
        return new BookingResource($booking->load('apartment'));
    }
// can a tenant cancel his booking if the booking don't start yet
    public function cancel(Request $request, Booking $booking)
    {
        Gate::authorize('cancel', $booking);
        if ($booking->status === 'confirmed' && Carbon::now()->greaterThanOrEqualTo($booking->start_date)) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Cannot cancel after booking start date'
            ], 400);
        }
    if (Carbon::today()->greaterThanOrEqualTo($booking->start_date)) {
    return response()->json([
        'status' => 'Error',
        'message' => 'Cannot cancel after booking start date'
    ], 400);
}
        $booking->update([
            'status'         => 'canceled',
            'owner_approved' => false,
        ]);
        return new BookingResource($booking->load('apartment'));
    }
// the owner approve of the booking to his apartment
    public function approve( Booking $booking)
    {
        Gate::authorize('approve', $booking);
        if ($this->checkingDate($booking->apartment_id, $booking->start_date, $booking->end_date, $booking->id)) {
            return response()->json(['status' => 'Error'], 409);
        }
        $booking->update([
            'owner_approved' => true,
            'status'         => 'confirmed',
        ]);
        return new BookingResource($booking->load('apartment'));
    }
// the owner reject the booking of his apartment
    public function reject(BookingRequest $request, Booking $booking)
    {
        Gate::authorize('reject', $booking);
        $booking->update([
            'owner_approved' => false,
            'status'         => 'canceled',
        ]);
        return new BookingResource($booking->load('apartment'));
    }
// the owner show the booking of his apartment that are pending his approved
    public function pendingBookingsForOwner(Request $request)
    {
        $bookings = Booking::with('apartment', 'tenant')
            ->whereHas('apartment', function ($query) use ($request) {
                $query->where('owner_id', $request->user()->id);
            })
            ->where('status', 'pending')
            ->orderBy('start_date', 'asc')
            ->get();
        return BookingResource::collection($bookings);
    }
}