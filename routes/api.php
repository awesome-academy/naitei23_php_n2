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
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Public routes (không cần đăng nhập)
    Route::post('/register', [AuthController::class, 'register']);  // Chỉ cho user đăng ký
    Route::post('/login', [AuthController::class, 'login']);        // Dùng chung cho tất cả roles
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']); // Reset password cho user
    Route::get('/verify-email', [AuthController::class, 'verifyEmail']); // Xác thực email (click từ email)
    Route::post('/resend-verification', [AuthController::class, 'resendVerification']); // Gửi lại email xác thực

    // Protected routes (cần đăng nhập)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Only Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // User management
    Route::get('/users', [UserController::class, 'index']);              // Danh sách users
    Route::post('/users', [UserController::class, 'store']);             // Tạo user mới (với role bất kỳ)
    Route::get('/users/{id}', [UserController::class, 'show']);          // Chi tiết user
    Route::put('/users/{id}', [UserController::class, 'update']);        // Cập nhật user
    Route::delete('/users/{id}', [UserController::class, 'destroy']);    // Xóa user
    Route::patch('/users/{id}/role', [UserController::class, 'updateRole']);     // Đổi role
    Route::patch('/users/{id}/toggle-active', [UserController::class, 'toggleActive']); // Bật/tắt active
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

// Giữ lại route cũ để tương thích ngược
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

        // Venue Services
        Route::get('venues/{venue}/services', [ServiceController::class, 'indexByVenue']);
        Route::post('venues/{venue}/services', [ServiceController::class, 'store']);
        Route::put('services/{service}', [ServiceController::class, 'update']);
        Route::delete('services/{service}', [ServiceController::class, 'destroy']);

        // Space Amenities
        Route::get('spaces/{space}/amenities', [SpaceAmenityController::class, 'index']);
        Route::put('spaces/{space}/amenities', [SpaceAmenityController::class, 'sync']);
    });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
