<?php
namespace App\Http\Controllers;

use App\Http\Requests\MyapartmentRequest;
use App\Http\Requests\SearchRequest;
use App\Models\Apartment;
use App\Http\Requests\StoreApartmentRequest;
use App\Http\Requests\UpdateApartmentRequest;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Request;
class ApartmentController extends Controller
{
        public function index()
    {
        Gate::authorize('viewAny', Apartment::class);

        $apartments = Apartment::with('images')->get();

        return response()->json([
            'data' => $apartments,
            'status' => 'success',
            'message' => 'Apartments indexed successfully.',
        ]);
    }
    public function store(StoreApartmentRequest $request)
    {
        Gate::authorize('create', Apartment::class);

        $apartment = Apartment::create($request->validated());

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('apartments', 'public');
                $apartment->images()->create(['image_path' => $path]);
            }
        }

        return response()->json([
            'data' => $apartment->load('images'),
            'status' => 'success',
            'message' => 'Apartment created successfully.',
        ], 201);
    }
    public function show(Apartment $apartment)
    {
        Gate::authorize('view', $apartment);

        $apartment->load('images');

        return response()->json([
            'data' => $apartment,
            'status' => 'success',
            'message' => 'Apartment retrieved successfully.',
        ]);
    }
    public function update(UpdateApartmentRequest $request, Apartment $apartment)
    {
        Gate::authorize('update', $apartment);

        $apartment->update($request->validated());

        return response()->json([
            'data' => $apartment->load('images'),
            'status' => 'success',
            'message' => 'Apartment updated successfully.',
        ]);
    }
    public function destroy(Apartment $apartment)
    {
        Gate::authorize('delete', $apartment);

        $apartment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Apartment deleted successfully.',
        ]);
    }
    public function search(SearchRequest $request)
    {
        Gate::authorize('viewAny', Apartment::class);

        $query = Apartment::query();

        if ($request->has('city')) {
            $query->where('city', $request->input('city'));
        }
        if ($request->has('country')) {
            $query->where('country', $request->input('country'));
        }
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }
        if ($request->has('number_of_room')) {
            $query->where('number_of_room', $request->input('number_of_room'));
        }
        if ($request->has('space')) {
            $query->where('space', $request->input('space'));
        }

        $apartments = $query->with('images')->get();

        return response()->json([
            'data' => $apartments,
            'status' => 'success',
            'message' => 'Search completed successfully.',
        ]);
    }
    public function myApartments(MyapartmentRequest $request)
{
    $owner=$request->user();
    $apartments = $owner->apartments()->with('images')->get();
    return response()->json([
        'data' => $apartments,
        'status' => 'success',
        'message' => 'Apartments retrieved successfully for this owner.',
    ]);
}
}