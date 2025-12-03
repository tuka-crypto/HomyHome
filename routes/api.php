<?php
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ApartmentImageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//auth routes
Route::post('/signup',[AuthController::class,'register']);
Route::post('/signin',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
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

// review route
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');