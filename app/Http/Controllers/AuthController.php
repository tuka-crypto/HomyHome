<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage as FacadesStorage;
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
                'message' => 'data is not valid',
                'errors' => $validator->errors()
            ], 422);
        }

        // رفع الصور
        $profilePath = $request->file('profile_image')->store('profiles', 'public');
        $idCardPath = $request->file('id_card_image')->store('id_cards', 'public');

        // إنشاء المستخدم
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
            'message' => 'register successfully, pending admin approval',
            'data' => $user
        ], 201);

    } catch (\Exception $e) {
        if (isset($profilePath)) FacadesStorage::disk('public')->delete($profilePath);
        if (isset($idCardPath)) FacadesStorage::disk('public')->delete($idCardPath);

        return response()->json([
            'status' => 'error',
            'message' => 'wrong in register',
            'error' => $e->getMessage()
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
            return response()->json([
                'message' => 'failed in login',
                'error' => $e->getMessage()
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
            return response()->json([
                'message' => 'failed in logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
