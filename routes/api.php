<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\Owner\VenueController;
use App\Http\Controllers\Owner\VenueAmenityController;
use App\Http\Controllers\Owner\ServiceController;
use App\Http\Controllers\Owner\SpaceAmenityController;
use App\Http\Controllers\Owner\OwnerSpaceController;
use App\Http\Controllers\Owner\OwnerVenueManagerController;
use App\Http\Controllers\Search\SearchSpaceController;
use App\Http\Controllers\PublicVenueController;
use App\Http\Controllers\PublicSpaceController;

use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\MapController;

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

/*
|--------------------------------------------------------------------------
| Map Routes (Public - Google Maps Integration)
|--------------------------------------------------------------------------
*/
Route::prefix('map')->group(function () {
    // Get map configuration (center, zoom, bounds)
    Route::get('/config', [MapController::class, 'config']);
    
    // Get all venue markers for map
    Route::get('/venues', [MapController::class, 'venues']);
    
    // Get venues within map bounds (viewport)
    Route::get('/venues/bounds', [MapController::class, 'venuesByBounds']);
    
    // Get venue detail for info window popup
    Route::get('/venues/{id}', [MapController::class, 'venueDetail']);
    
    // Search venues on map
    Route::get('/search', [MapController::class, 'search']);
});

/*
|--------------------------------------------------------------------------
| Public Detail Routes
|--------------------------------------------------------------------------
*/
Route::get('venues/{id}', [PublicVenueController::class, 'show']);
Route::get('spaces/{id}', [PublicSpaceController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Search Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('search')->group(function () {
    Route::get('spaces', [SearchSpaceController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Recommendation Routes (Public - Geolocation Based)
|--------------------------------------------------------------------------
*/
Route::prefix('recommendations')->group(function () {
    // Get nearby venues based on user's geolocation
    Route::get('/nearby', [RecommendationController::class, 'nearbyVenues']);
    
    // Get venues in a specific city
    Route::get('/city', [RecommendationController::class, 'venuesByCity']);
    
    // Get list of available cities
    Route::get('/cities', [RecommendationController::class, 'availableCities']);
    
    // Get popular/featured venues
    Route::get('/popular', [RecommendationController::class, 'popularVenues']);
});

/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Public routes (không cần đăng nhập)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);

    // Protected routes (cần đăng nhập)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| Booking Routes (User - Auth Required)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\BookingController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Only Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // User management
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::patch('/users/{id}/role', [UserController::class, 'updateRole']);
    Route::patch('/users/{id}/toggle-active', [UserController::class, 'toggleActive']);
});

/*
|--------------------------------------------------------------------------
| Moderator Routes (Admin & Moderator)
|--------------------------------------------------------------------------
*/
Route::prefix('moderator')->middleware(['auth:sanctum', 'role:admin,moderator'])->group(function () {
    // Thêm các routes cho moderator ở đây sau này
    // Ví dụ: quản lý venues, bookings, etc.
});

/*
|--------------------------------------------------------------------------
| Owner Routes (Venue Management)
|--------------------------------------------------------------------------
*/
// Global amenities list (public/shared)
Route::get('amenities', [AmenityController::class, 'index']);

// Owner routes - NOW USING SANCTUM AUTH
Route::middleware(['auth:sanctum'])
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

        // Venue Services
        Route::get('venues/{venue}/services', [ServiceController::class, 'indexByVenue']);
        Route::post('venues/{venue}/services', [ServiceController::class, 'store']);
        Route::put('services/{service}', [ServiceController::class, 'update']);
        Route::delete('services/{service}', [ServiceController::class, 'destroy']);

        // Space CRUD
        Route::get('venues/{venue}/spaces', [OwnerSpaceController::class, 'index']);
        Route::post('venues/{venue}/spaces', [OwnerSpaceController::class, 'store']);
        Route::get('spaces/{space}', [OwnerSpaceController::class, 'show']);
        Route::put('spaces/{space}', [OwnerSpaceController::class, 'update']);
        Route::delete('spaces/{space}', [OwnerSpaceController::class, 'destroy']);

        // Space Amenities
        Route::get('spaces/{space}/amenities', [SpaceAmenityController::class, 'index']);
        Route::put('spaces/{space}/amenities', [SpaceAmenityController::class, 'sync']);

        // Venue Managers
        Route::get('venues/{venue}/managers', [OwnerVenueManagerController::class, 'index']);
        Route::post('venues/{venue}/managers', [OwnerVenueManagerController::class, 'store']);
        Route::delete('venues/{venue}/managers/{user}', [OwnerVenueManagerController::class, 'destroy']);

        // Booking Management (Owner/Manager)
        Route::get('bookings', [\App\Http\Controllers\Owner\OwnerBookingController::class, 'index']);
        Route::get('venues/{venue}/bookings', [\App\Http\Controllers\Owner\OwnerBookingController::class, 'bookingsByVenue']);
        Route::get('spaces/{space}/bookings', [\App\Http\Controllers\Owner\OwnerBookingController::class, 'bookingsBySpace']);
        Route::patch('bookings/{booking}/confirm', [\App\Http\Controllers\Owner\OwnerBookingController::class, 'confirm']);
        Route::patch('bookings/{booking}/reject', [\App\Http\Controllers\Owner\OwnerBookingController::class, 'reject']);
    });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
