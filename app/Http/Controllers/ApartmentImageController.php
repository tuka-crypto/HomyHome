<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Apartment_image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApartmentImageController extends Controller
{
    public function index(Apartment $apartment)
    {
        return response()->json([
            'data' => $apartment->images,
            'status' => 'success',
            'message' => 'Images retrieved successfully.',
        ]);
    }

    public function store(Request $request, Apartment $apartment)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = $request->file('image')->store('apartments', 'public');

        $image = $apartment->images()->create([
            'image_path' => $path,
        ]);

        return response()->json([
            'data' => $image,
            'status' => 'success',
            'message' => 'Image uploaded successfully.',
        ]);
    }

    public function destroy(Apartment $apartment, Apartment_image $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Image deleted successfully.',
        ]);
    }
}

