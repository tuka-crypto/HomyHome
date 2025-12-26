<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApartmentResource;
use App\Models\Apartment;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Apartment $apartment)
    {
        $user = $request->user();
        if ($apartment->status !== 'approved') {
            return response()->json([
                'status'  => 'error',
                'message' => __('messages.apartment_not_approved'),
            ], 403);
        }
        if ($user->favorites()->where('apartment_id', $apartment->id)->exists()) {
            $user->favorites()->detach($apartment->id);
            return response()->json([
                'status'  => 'success',
                'message' => __('messages.favorite_removed'),
            ]);
        }
        $user->favorites()->attach($apartment->id);
        return response()->json([
            'status'  => 'success',
            'message' => __('messages.favorite_added'),
        ]);
    }
    public function index(Request $request)
    {
        return response()->json([
        'status' => 'success',
        'data'   => ApartmentResource::collection(
            $request->user()->favorites()->get()
        ),
    ]);
    }
}
