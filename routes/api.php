<?php
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ApartmentImageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//auth routes
Route::post('/signup',[AuthController::class,'signup']);
Route::post('/signin',[AuthController::class,'signin']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
//approved and reject the admin to auth
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/users/pending', [AuthController::class, 'pendingUsers']);
    Route::put('/users/{user}/approve', [AuthController::class, 'approveUser']);
    Route::put('/users/{user}/reject', [AuthController::class, 'rejectUser']);
    Route::delete('/users/{user}', [AuthController::class, 'deleteUser']);
});

//apartment routes for all
    Route::get('/apartments', [ApartmentController::class, 'index']);
    Route::get('/apartments/{apartment}', [ApartmentController::class, 'show']);
    Route::get('/apartments/search', [ApartmentController::class, 'search']);

// Owner-only routes about apartments
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    Route::get('/apartments/my', [ApartmentController::class, 'myApartments']);
    Route::post('/apartments', [ApartmentController::class, 'store']);
    Route::put('/apartments/{apartment}', [ApartmentController::class, 'update']);
    Route::delete('/apartments/{apartment}', [ApartmentController::class, 'destroy']);
});

// images routes for apartment images(index, store,delete)
Route::get('/apartments/{apartment}/images', [ApartmentImageController::class, 'index']);
Route::post('/apartments/{apartment}/images', [ApartmentImageController::class, 'store']);
Route::delete('/apartments/{apartment}/images/{image}', [ApartmentImageController::class, 'destroy']);

// booking routes for tenant
Route::middleware(['auth:sanctum', 'role:tenant'])->group(function () {
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::put('/bookings/{booking}', [BookingController::class, 'update']);
    Route::get('/bookings/my', [BookingController::class, 'myBookings']);
});
// approved and reject owner to booking
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    Route::get('/bookings/pending', [BookingController::class, 'pendingBookingsForOwner']);
    Route::put('/bookings/{booking}/approve', [BookingController::class, 'approve']);
    Route::put('/bookings/{booking}/reject', [BookingController::class, 'reject']);
});
// review route
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');