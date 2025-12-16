<?php
namespace App\Http\Controllers;

use App\Http\Requests\AdminloginRequest;
use App\Http\Requests\OtpRequestue;
use App\Http\Requests\SigninRequest;
use App\Http\Requests\SignupRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
class AuthController extends Controller
{
// sign up the customer to the first time and choose his rule :tenant ,owner
    public function signup(SignupRequest $request)
{
    try {
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
        'data' => new UserResource($user)
        ], 201);
    } catch (\Exception $e) {
        if (isset($profilePath)) Storage::disk('public')->delete($profilePath);
        if (isset($idCardPath)) Storage::disk('public')->delete($idCardPath);
        Log::error($e);
        return response()->json([
            'status' => 'Error',
            'message' => 'Error in the sign up , try again'
        ], 500);
    }
}
// operation of send the otp code
public function sendOTP(User $user){
        $otp = rand(100000, 999999);
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5),
        ]);
        $instanceId = env('ULTRAMSG_INSTANCE_ID');
        $token = env('ULTRAMSG_TOKEN');
        Http::post("https://api.ultramsg.com/$instanceId/messages/chat", [
            'token' => $token,
            'to'    => $user->mobile_phone,
            'body'  => "your otp code is :$otp",
        ]);
}
//admin login and send the otp code to his whatsapp
public function adminLogin(AdminloginRequest $request)
{
    try {
        $user = User::where('mobile_phone', $request->mobile_phone)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Access denied. Admin only.'], 403);
        }
        $this->sendOTP($user);
        return response()->json(['message' => 'send the code to whatsapp']);
    } catch (\Exception $e) {
        Log::error($e);
        return response()->json(['message' => 'Error in login , try again'], 500);
    }
}
// sign in after admin approved and send the otp code
public function signin(SigninRequest $request)
{
    try {
        $user = User::where('mobile_phone', $request->mobile_phone)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        if (!$user->is_approved) {
            return response()->json(['message' => 'pending admin approved'], 403);
        }
        $this->sendOTP($user);
        return response()->json(['message' => 'send the code to whatsapp']);
    } catch (\Exception $e) {
        Log::error($e);
        return response()->json(['message' => 'Error in sign in , try again'], 500);
    }
}
//logout the user from  the current token
public function logout(Request $request)
    {
        try {
            if ($request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }
            return response()->json(['message' => 'logout successfully']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'Error in logout, try again'], 500);
        }
    }
//verify fron otp that send it to the user in whatsapp
public function verifyOtp(OtpRequestue $request)
{
    $user = User::where('mobile_phone', $request->mobile_phone)->first();
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }
    if ((string)$user->otp_code !== (string)$request->otp_code) {
        return response()->json(['message' => 'Invalid OTP'], 401);
    }
    if (Carbon::now()->greaterThan($user->otp_expires_at)) {
        return response()->json(['message' => 'OTP expired'], 403);
    }
    $user->update([
        'otp_code' => null,
        'otp_expires_at' => null,
    ]);
    $user->tokens()->delete();
    $token = $user->createToken('auth_token')->plainTextToken;
    return response()->json([
    'token' => $token,
    'user' => new UserResource($user),
]);
}
//the admin know how the num of user in the app
public function usersCount(Request $request)
{
    if (!$request->user()->isAdmin()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    $count = User::where('role', '!=', 'admin')->count();
    return response()->json([
        'status'  => 'success',
        'message' => 'the num of users retrieved successfully.',
        'count'   => $count
    ]);
}
//user with pending status waiting to approve from admin
    public function pendingUsers(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $users = User::where('is_approved', false)->get();
        return response()->json([
            'status' => 'success',
            'data'   => UserResource::collection($users),
            'message'=> 'Pending users retrieved successfully.'
        ]);
    }
// admin approved to the user
    public function approveUser(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $user->update(['is_approved' => true]);
        return response()->json([
            'status'  => 'success',
            'message' => 'User approved successfully.',
            'data'    => new UserResource($user)
        ]);
    }
//admin reject the user
    public function rejectUser(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $user->update(['is_approved' => false]);
        return response()->json([
            'status'  => 'success',
            'message' => 'User rejected successfully.',
            'data'    => new UserResource($user)
        ]);
    }
//admin delete the user
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

