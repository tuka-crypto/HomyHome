<?php
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');

//  index route available to both tenant and owner
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/apartments', [ApartmentController::class, 'index']);
});

// Tenant-only routes
Route::middleware(['auth:sanctum', 'role:tenant'])->group(function () {
    Route::get('/apartments/{apartment}', [ApartmentController::class, 'show']);
    Route::get('/apartments/search', [ApartmentController::class, 'search']);
});

// Owner-only routes
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    Route::post('/apartments', [ApartmentController::class, 'store']);
    Route::put('/apartments/{apartment}', [ApartmentController::class, 'update']);
    Route::delete('/apartments/{apartment}', [ApartmentController::class, 'destroy']);
});
// review route
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');