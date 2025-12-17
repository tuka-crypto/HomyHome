<?php
namespace App\Http\Controllers;
use App\Http\Requests\MyapartmentRequest;
use App\Http\Requests\SearchRequest;
use App\Models\Apartment;
use App\Http\Requests\StoreApartmentRequest;
use App\Http\Requests\UpdateApartmentRequest;
use App\Http\Resources\ApartmentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApartmentController extends Controller
{
// show all the apartment to the user and with if statment that the status of apartment is approved
    public function index()
    {
        Gate::authorize('viewAny', Apartment::class);
        $apartments = Apartment::with('images')
            ->where('status', 'approved')
            ->get();
        return ApartmentResource::collection($apartments);
    }
// add the owner the apartment and waiting approved from admin
    public function store(StoreApartmentRequest $request)
    {
        Gate::authorize('create', Apartment::class);
        $apartment = Apartment::create(array_merge(
            $request->validated(),
            [   'status' => 'pending',
                'owner_id' => $request->user()->id,
                'city'=> $request->input('city'),
                'country'=> $request->input('country'),
                'address'=> $request->input('address'),
                'price'=> $request->input('price'),
                'number_of_room'=> $request->input('number_of_room'),
                'space'=> $request->input('space'),
                'description'=> $request->input('description'),
                'is_available'=>$request->input('is_available')
            ]
        ));
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('apartments', 'public');
                $apartment->images()->create(['image_path' => $path]);
            }
        }
        $apartment = Apartment::with('images')->find($apartment->id);
        return new ApartmentResource($apartment);
    }
// show the user one apartment and with if statment that the status of apartment is approved
    public function show(Apartment $apartment)
    {
        Gate::authorize('view', $apartment);
        if ($apartment->status !== 'approved') {
            return response()->json([
                'status' => 'error',
                'message' => 'Apartment not approved yet.',
            ], 403);
        }
        $apartment->load('images');
        return new ApartmentResource($apartment);
    }
//update the owner his apartment and waiting the approved from admin
    public function update(UpdateApartmentRequest $request, Apartment $apartment)
{
    Gate::authorize('update', $apartment);
    $data = $request->only([
        'price',
        'number_of_room',
        'space',
        'description',
        'is_available'
    ]);
    $data['status'] = 'pending';
    $apartment->update($data);
    return new ApartmentResource($apartment->load('images'));
}
// the owner delete his apartment
    public function destroy(Apartment $apartment)
    {
        Gate::authorize('delete', $apartment);
        $apartment->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Apartment deleted successfully.',
        ]);
    }
// the user can search about what he want
    public function search(SearchRequest $request)
    {
        Gate::authorize('viewAny', Apartment::class);
        $query = Apartment::query()->where('status', 'approved');
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
        return ApartmentResource::collection($apartments);
    }
// the owner can show his apartment
    public function myApartments(MyapartmentRequest $request)
    {
        $owner = $request->user();
        $apartments = $owner->apartments()->with('images')->get();
        return ApartmentResource::collection($apartments);
    }
//the admin know how the num of pending apartments in the app
public function ApartmentCount(Request $request)
{
    if (!$request->user()->isAdmin()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    $count = Apartment::where('status', 'pending')->count();
    return response()->json([
        'status'  => 'success',
        'message' => 'the num of pending apartments retrieved successfully.',
        'count'   => $count
    ]);
}
// the admin show the apartment needing his approved
public function pendingApartments(Request $request)
{
        Gate::authorize('viewPending', Apartment::class);
        $apartments = Apartment::where('status', 'pending')->with('images')->get();
        return ApartmentResource::collection($apartments);
}
// the admin approved to add/update the apartment
    public function approve(Apartment $apartment)
    {
        Gate::authorize('approve', $apartment);
        $apartment->update(['status' => 'approved']);
        return new ApartmentResource($apartment->load('images'));
    }
// the admin rejected to add/update the apartment
    public function reject(Apartment $apartment)
    {
        Gate::authorize('reject', $apartment);
        $apartment->update(['status' => 'rejected']);
        return new ApartmentResource($apartment->load('images'));
    }
}