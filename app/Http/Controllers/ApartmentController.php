<?php

namespace App\Http\Controllers;

use App\Http\Requests\MyapartmentRequest;
use App\Http\Requests\SearchRequest;
use App\Models\Apartment;
use App\Http\Requests\StoreApartmentRequest;
use App\Http\Requests\UpdateApartmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApartmentController extends Controller
{
    // ✅ عرض كل الشقق (لأي مستخدم)
    public function index()
    {
        Gate::authorize('viewAny', Apartment::class);

        $apartments = Apartment::with('images')
            ->where('status', 'approved') // فقط الشقق الموافق عليها
            ->get();

        return response()->json([
            'data' => $apartments,
            'status' => 'success',
            'message' => 'Apartments indexed successfully.',
        ]);
    }

    // ✅ المالك يطلب إضافة شقة (تدخل كـ pending)
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
                'discreption'=> $request->input('discreption'),
                'is_available'=>$request->input('is_available')
            ]
        ));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('apartments', 'public');
                $apartment->images()->create(['image_path' => $path]);
            }
        }

        return response()->json([
            'data' => $apartment->load('images'),
            'status' => 'success',
            'message' => 'Apartment request submitted. Waiting for admin approval.',
        ], 201);
    }

    // ✅ عرض شقة واحدة
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

        return response()->json([
            'data' => $apartment,
            'status' => 'success',
            'message' => 'Apartment retrieved successfully.',
        ]);
    }

    // ✅ المالك يعدل شقته (لكن تظل بحاجة موافقة جديدة)
   public function update(UpdateApartmentRequest $request, Apartment $apartment)
{
    Gate::authorize('update', $apartment);

    $data = $request->only([
        'city',
        'country',
        'address',
        'price',
        'number_of_room',
        'space',
        'discreption',
        'is_available'
    ]);

    // أضف الحالة pending دائمًا
    $data['status'] = 'pending';

    $apartment->update($data);

    return response()->json([
        'data' => $apartment->load('images'),
        'status' => 'success',
        'message' => 'Apartment update submitted. Waiting for admin approval.',
    ]);
}
    // ✅ المالك يحذف شقته
    public function destroy(Apartment $apartment)
    {
        Gate::authorize('delete', $apartment);

        $apartment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Apartment deleted successfully.',
        ]);
    }

    // ✅ البحث في الشقق (فقط الموافق عليها)
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

        return response()->json([
            'data' => $apartments,
            'status' => 'success',
            'message' => 'Search completed successfully.',
        ]);
    }

    // ✅ المالك يشوف شققه الخاصة
    public function myApartments(MyapartmentRequest $request)
    {
        $owner = $request->user();
        $apartments = $owner->apartments()->with('images')->get();

        return response()->json([
            'data' => $apartments,
            'status' => 'success',
            'message' => 'Apartments retrieved successfully for this owner.',
        ]);
    }
    // ✅ الأدمن يشوف كل الشقق المعلقة (pending)
public function pendingApartments(Request $request)
{
     if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $apartments = Apartment::where('status', 'pending')->get();
    return response()->json([
        'data'    => $apartments,
        'status'  => 'success',
        'message' => 'Pending apartments retrieved successfully.',
    ]);
}

    // ✅ الأدمن يوافق على شقة
    public function approve(Apartment $apartment)
    {
        Gate::authorize('approve', $apartment);

        $apartment->update(['status' => 'approved']);

        return response()->json([
            'data' => $apartment->load('images'),
            'status' => 'success',
            'message' => 'Apartment approved by admin.',
        ]);
    }

    // ✅ الأدمن يرفض شقة
    public function reject(Apartment $apartment)
    {
        Gate::authorize('reject', $apartment);

        $apartment->update(['status' => 'rejected']);

        return response()->json([
            'data' => $apartment->load('images'),
            'status' => 'success',
            'message' => 'Apartment rejected by admin.',
        ]);
    }
}