<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Apartment;
use App\Http\Requests\StoreApartmentRequest;
use App\Http\Requests\UpdateApartmentRequest;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Symfony\Component\HttpFoundation\Request;

class ApartmentController extends Controller
{
   
    public function index()
    {   
        if(User::isOwner()){
            return response()->json([
                'status' => 'error',
                'message' => 'Only tenant can view apartments.',
            ], 403);
        }
        $apartments = Apartment::all();
        return response()->json([
        'data' => $apartments,
        'status' => 'success',
        'message' => 'Apartments indexed successfully.',
        ]);
    }

    
    public function store(StoreApartmentRequest $request)
    {
        if(User::isOwner()){
            $apartment = Apartment::create($request->all());
            return response()->json([
                'data' => $apartment,
                'status' => 'success',
                'message' => 'Apartment created successfully.',
            ], 201);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Only owners can create apartments.',
            ], 403);
    }

}
  public function show(Apartment $apartment)
{
    if(User::isOwner()){
        return response()->json([
            'data' => $apartment,
            'status' => 'success',
            'message' => 'Apartment retrieved successfully.',
        ], 200);
    }else{
        return response()->json([
            'status' => 'error',
            'message' => 'Only owners can view apartment details.',
        ], 403);}
}
    public function update(UpdateApartmentRequest $request, Apartment $apartment)
    {
        if(User::isOwner()){
            $apartment->update($request->all());
            return response()->json([
                'data' => $apartment,
                'status' => 'success',
               ' message' => 'Apartment updated successfully.',
            ], 201);
        }else{
            return response()->json([
                'status' => 'error',
               ' message' => 'Only owners can update apartments.',
            ], 403);
    }
    }

   
    public function destroy(Apartment $apartment)
    {
        if(User::isOwner()){
            $apartment->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Apartment deleted successfully.',
            ], 200);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Only owners can delete apartments.',
            ], 403  );        
    }
    }
    public function search(SearchRequest $request)
    {
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

        $apartments = $query->get();

        return response()->json([
            'data' => $apartments,
            'status' => 'success',
            'message' => 'Search completed successfully.',
        ]);
    }
}
