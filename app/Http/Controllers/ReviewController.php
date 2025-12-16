<?php
namespace App\Http\Controllers;
use App\Models\Review;
use App\Models\Booking;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'booking_id'   => 'required|exists:bookings,id',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string',
        ]);
        $booking = Booking::where('id', $request->booking_id)
            ->where('tenant_id', Auth::id())
            ->where('apartment_id', $request->apartment_id)
            ->first();
        if (!$booking) {
            return response()->json([
                'message' => 'you cannot add a review if you donnot booked this apartment'
            ], 403);
        }
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'message' => 'you can only review after approved your booking'
            ], 403);
        }
        $alreadyReviewed = Review::where('booking_id', $request->booking_id)
            ->where('tenant_id', Auth::id())
            ->exists();
        if ($alreadyReviewed) {
            return response()->json([
                'message' => 'you have already reviewed this apartment'
            ], 400);
        }
        $review = Review::create([
            'apartment_id' => $request->apartment_id,
            'tenant_id'    => Auth::id(),
            'booking_id'   => $request->booking_id,
            'rating'       => $request->rating,
            'comment'      => $request->comment,
        ]);
        $apartment = Apartment::find($request->apartment_id);
        $apartment->average_rating = $apartment->reviews()->avg('rating');
        $apartment->save();
        return response()->json([
            'message' => 'reviewed successfully',
            'review'  => $review->load('apartment')
        ]);
    }
}