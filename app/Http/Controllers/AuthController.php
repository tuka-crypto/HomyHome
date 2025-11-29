<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Hash;
use Illuminate\Support\Facades\Hash as FacadesHash;
use Illuminate\Testing\Fluent\Concerns\Has;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function register(Request $request)
    {
        $request->validate([
            'mobile_phone' => 'required|string|unique:users',
            'password' => 'required|string|min:4',
            'first_name'=>'required|string|max:255',
            'last_name'=>'required|string|max:255',
            'role'=>'required|in:owner,tenant,admin',
            'date_of_birth'=>'nullable|date',
            'profile_image'=>'nullable|string|max:255',
            'id_card_image'=>'nullable|string|max:255',
        ]);
        $user = User::query()->create([
            'mobile_phone' => $request->mobile_phone,
            'password' => FacadesHash::make($request->password),
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
            'role'=>$request->role,
            'date_of_birth'=>$request->date_of_birth,
            'profile_image'=>$request->profile_image,
            'id_card_image'=>$request->id_card_image,
        ]); 
           $token=$user->createToken("API TOKEN")->plainTextToken;
    $data=[];
    $data['user']=$user;
    $data['token']=$token;
    return response()->json([
        'status'=>1,
        'data'=>$data,
        'massege'=>'user created successfully'
    ]);
    }
    public function login(Request $request)
    {
        //still the admin approvment is pending
        $request->validate([
            'mobile_phone' => 'required|string',
            'password' => 'required|string|min:4',
        ]);
        $user = User::where('mobile_phone', $request->mobile_phone)->first();
        if (!$user || !FacadesHash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 0,
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }
        $token = $user->createToken("API TOKEN")->plainTextToken;
        $data = [];
        $data['user'] = $user;
        $data['token'] = $token;
        return response()->json([
            'status' => 1,
            'data' => $data,
            'message' => 'User logged in successfully'
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'status' => 1,
            'message' => 'User logged out successfully'
        ]);
    }
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
