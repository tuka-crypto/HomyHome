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
use App\Services\NotificationService;
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
    public function store(BookingRequest $request , NotificationService $notify)
    {
        Gate::authorize('create', Booking::class);
        $apartment = Apartment::findOrFail($request['apartment_id']);
        if ($apartment->status !== 'approved' || !$apartment->is_available) {
            return response()->json([
                'status' => 'Error',
                'message' =>__('messages.booking_not_available')
            ], 403);
        }
        if ($this->checkingDate($apartment->id, $request['start_date'], $request['end_date'])) {
            return response()->json([
                'status' => 'Error',
                'message'=>__('messages.booking_overlap')
            ], 409);
        }
        $days = (new \DateTime($request['end_date']))->diff(new \DateTime($request['start_date']))->days;
        if ($days < 1) {
        return response()->json([
        'status' => 'Error',
        'message' =>__('messages.booking_min_night')
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
        $notify->sendToUser(
            $apartment->owner,
            'notify_new_booking_title',
            'notify_new_booking_body',
            [
                'name'      => $request->user()->first_name,
                'apartment' => $apartment->id,
                'booking'   => $booking->id,
            ]
        );
        return new BookingResource($booking->load('apartment'));
    }
//update his booking (start_date,end_date,guest_count)
    public function update(UpdateBookingRequest $request, Booking $booking,NotificationService $notify)
    {
        Gate::authorize('update', $booking);
        if (isset($request['start_date'], $request['end_date']) &&
            $this->checkingDate($booking->apartment_id, $request['start_date'], $request['end_date'], $booking->id)) {
            return response()->json([
                'status' => 'Error',
                'message' =>__('messages.booking_updated')
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
    $notify->sendToUser(
            $booking->apartment->owner,
            'notify_booking_updated_title',
            'notify_booking_updated_body',
            [
                'name'    => $booking->tenant->first_name,
                'booking' => $booking->id,
            ]
        );
        return new BookingResource($booking->load('apartment'));
    }
// can a tenant cancel his booking if the booking don't start yet
    public function cancel(Request $request, Booking $booking,NotificationService $notify)
    {
        Gate::authorize('cancel', $booking);
        if ($booking->status === 'confirmed' && Carbon::now()->greaterThanOrEqualTo($booking->start_date)) {
            return response()->json([
                'status' => 'Error',
                'message' =>__('messages.booking_cannot_cancel')
            ], 400);
        }
    if (Carbon::today()->greaterThanOrEqualTo($booking->start_date)) {
    return response()->json([
        'status' => 'Error',
        'message' =>__('messages.booking_cannot_cancel')
    ], 400);
}
        $booking->update([
            'status'         => 'canceled',
            'owner_approved' => false,
        ]);
        $notify->sendToUser(
            $booking->apartment->owner,
            'notify_booking_canceled_title',
            'notify_booking_canceled_body',
            [
                'name'    => $booking->tenant->first_name,
                'booking' => $booking->id,
            ]
        );
        return new BookingResource($booking->load('apartment'));
    }
// the owner approve of the booking to his apartment
    public function approve( Booking $booking,NotificationService $notify)
    {
        Gate::authorize('approve', $booking);
        if ($this->checkingDate($booking->apartment_id, $booking->start_date, $booking->end_date, $booking->id)) {
            return response()->json(['status' => 'Error'], 409);
        }
        $booking->update([
            'owner_approved' => true,
            'status'         => 'confirmed',
        ]);
        $notify->sendToUser(
            $booking->tenant,
            'notify_booking_approved_title',
            'notify_booking_approved_body',
            [
                'booking' => $booking->id,
            ]
        );
        return new BookingResource($booking->load('apartment'));
    }
// the owner reject the booking of his apartment
    public function reject(BookingRequest $request, Booking $booking , NotificationService $notify)
    {
        Gate::authorize('reject', $booking);
        $booking->update([
            'owner_approved' => false,
            'status'         => 'canceled',
        ]);
        $notify->sendToUser(
            $booking->tenant,
            'notify_booking_rejected_title',
            'notify_booking_rejected_body',
            [
                'booking' => $booking->id,
            ]
        );
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