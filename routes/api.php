<?php
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ApartmentImageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//auth routes
Route::post('/signup',[AuthController::class,'signup']);
Route::post('/signin',[AuthController::class,'signin']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
// language
Route::middleware(['auth:sanctum', 'locale'])->group(function () {
//FCM notification
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/language', [UserSettingsController::class, 'updateLanguage']);
    Route::post('/user/theme', [UserSettingsController::class, 'updateTheme']);
    Route::post('/user/fcm-token', [UserSettingsController::class, 'updateFcmToken']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});
//approved and reject the admin to auth
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/users/pending', [AuthController::class, 'pendingUsers']);
    Route::get('/users/count', [AuthController::class, 'usersCount']);
    Route::put('/users/{user}/approve', [AuthController::class, 'approveUser']);
    Route::put('/users/{user}/reject', [AuthController::class, 'rejectUser']);
    Route::delete('/users/{user}', [AuthController::class, 'deleteUser']);
    Route::get('/apartments/pending', [ApartmentController::class, 'pendingApartments']);
    Route::get('/apartments/count', [ApartmentController::class, 'ApartmentCount']);
});
Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/apartments', [ApartmentController::class, 'index']);
    Route::get('/apartments/search', [ApartmentController::class, 'search']);
});
Route::middleware(['auth:sanctum','owner'])->group(function() {
    Route::post('/apartments', [ApartmentController::class, 'store']);
    Route::get('/apartments/my', [ApartmentController::class, 'myApartments']);
});
Route::middleware(['auth:sanctum','admin'])->group(function() {
    Route::post('/apartments/{apartment}/approve', [ApartmentController::class, 'approve']);
    Route::post('/apartments/{apartment}/reject', [ApartmentController::class, 'reject']);
});
//apartment routes for all
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/apartments/{apartment}', [ApartmentController::class, 'show']);
});

// Owner-only routes about apartments
Route::middleware(['auth:sanctum', 'owner'])->group(function () {
    Route::put('/apartments/{apartment}', [ApartmentController::class, 'update']);
    Route::delete('/apartments/{apartment}', [ApartmentController::class, 'destroy']);
});

// images routes for apartment images(index, store,delete)
Route::get('/apartments/{apartment}/images', [ApartmentImageController::class, 'index']);
Route::post('/apartments/{apartment}/images', [ApartmentImageController::class, 'store']);
Route::delete('/apartments/{apartment}/images/{image}', [ApartmentImageController::class, 'destroy']);

// booking routes for tenant
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::put('/bookings/{booking}', [BookingController::class, 'update']);
    Route::delete('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
});

// approved and reject owner to booking
Route::middleware(['auth:sanctum', 'owner'])->group(function () {
    Route::get('/owner/pending-bookings', [BookingController::class, 'pendingBookingsForOwner']);
    Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve']);
    Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject']);
});
// review route
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);
});
});
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');