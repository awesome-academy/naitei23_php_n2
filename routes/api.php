<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\Owner\VenueController;
use App\Http\Controllers\Owner\VenueAmenityController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Global amenities list (public/shared)
Route::get('amenities', [AmenityController::class, 'index']);

// Owner routes - Venue CRUD
// TODO: Replace 'fake.auth' with 'auth:sanctum' when authentication is ready
Route::middleware('fake.auth')
    ->prefix('owner')
    ->group(function () {
        // Venue CRUD
        Route::get('venues', [VenueController::class, 'index']);
        Route::post('venues', [VenueController::class, 'store']);
        Route::get('venues/{venue}', [VenueController::class, 'show']);
        Route::put('venues/{venue}', [VenueController::class, 'update']);
        Route::delete('venues/{venue}', [VenueController::class, 'destroy']);

        // Venue Amenities
        Route::get('venues/{venue}/amenities', [VenueAmenityController::class, 'index']);
        Route::put('venues/{venue}/amenities', [VenueAmenityController::class, 'sync']);
    });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
