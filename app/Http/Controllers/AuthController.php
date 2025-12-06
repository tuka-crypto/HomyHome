<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function signup(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'mobile_phone' => 'required|unique:users|regex:/^[0-9]{10,15}$/',
            'password' => 'required|min:6',
            'role' => 'required|in:tenant,owner',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date|before:today',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'id_card_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'data is invalid',
                'errors' => $validator->errors()
            ], 422);
        }
        $profilePath = $request->file('profile_image')->storeAs(
            'profiles',
            uniqid().'_'.$request->file('profile_image')->getClientOriginalName(),
            'public'
        );

        $idCardPath = $request->file('id_card_image')->storeAs(
            'id_cards',
            uniqid().'_'.$request->file('id_card_image')->getClientOriginalName(),
            'public'
        );
        $user = User::create([
            'mobile_phone' => $request->mobile_phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_approved' => false,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'profile_image' => $profilePath,
            'id_card_image' => $idCardPath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'sign up is successfully, waiting admin approved',
            'data' => $user
        ], 201);

    } catch (\Exception $e) {
        if (isset($profilePath)) Storage::disk('public')->delete($profilePath);
        if (isset($idCardPath)) Storage::disk('public')->delete($idCardPath);

        Log::error($e);

        return response()->json([
            'status' => 'error',
            'message' => 'wrong in the sign up , try again'
        ], 500);
    }
}
public function adminLogin(Request $request)
{
    try {
        $request->validate([
            'mobile_phone' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('mobile_phone', $request->mobile_phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Access denied. Admin only.'
            ], 403);
        }
        $user->tokens()->delete();
        $token = $user->createToken('admin_token')->plainTextToken;
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'mobile_phone' => $user->mobile_phone,
                'role' => $user->role,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error($e);
        return response()->json([
            'message' => 'Error in admin login'
        ], 500);
    }
}
    public function signin(Request $request)
    {
        try {
            $request->validate([
                'mobile_phone' => 'required',
                'password' => 'required'
            ]);

            $user = User::where('mobile_phone', $request-> mobile_phone)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'mobile_phone' => ['data is not correct'],
                ]);
            }

            if (!$user->is_approved) {
                return response()->json([
                    'message' => 'pending admin approval'
                ], 403);
            }
            $user->tokens()->delete();
            
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'mobile_phone' => $user->mobile_phone,
                    'role' => $user->role,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'has_completed_profile' => !is_null($user->first_name) && !is_null($user->last_name)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error($e);
        return response()->json([
            'message' => 'wrong in sing in , try again'
        ], 500);

        }
    }

public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            
            return response()->json([
                'message' => 'logout successfully'
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
            'message' => 'wrong in logout ,try again'
            ], 500);
        }
    }

    public function pendingUsers(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::where('is_approved', false)->get();

        return response()->json([
            'status' => 'success',
            'data'   => $users,
            'message'=> 'Pending users retrieved successfully.'
        ]);
    }
    public function approveUser(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->update(['is_approved' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => 'User approved successfully.',
            'data'    => $user
        ]);
    }
    public function rejectUser(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->update(['is_approved' => false]);

        return response()->json([
            'status'  => 'success',
            'message' => 'User rejected successfully.',
            'data'    => $user
        ]);
    }
    public function deleteUser(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'User deleted successfully.'
        ]);
    }
}

