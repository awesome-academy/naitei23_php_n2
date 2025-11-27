<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Owner\VenueController;

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

// Owner routes - Venue CRUD
// TODO: Uncomment auth:sanctum middleware when authentication is ready
Route::prefix('owner')
    ->group(function () {
        Route::get('venues', [VenueController::class, 'index']);
        Route::post('venues', [VenueController::class, 'store']);
        Route::get('venues/{venue}', [VenueController::class, 'show']);
        Route::put('venues/{venue}', [VenueController::class, 'update']);
        Route::delete('venues/{venue}', [VenueController::class, 'destroy']);
    });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
