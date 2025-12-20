<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
// use App\Http\Controllers\LanguageController; // Comment tạm
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
   return view('homepage');
});

// Register routes - from test/api-checklist branch
Route::get('/register', [AuthController::class, 'create']);
Route::post('/register', [AuthController::class, 'store']);

// AUTO-LOGIN routes for testing
Route::get('/auto-login', function () {
    $user = \App\Models\User::where('email', 'owner@workspace.com')->first();

    if (!$user) {
        $user = \App\Models\User::first();
    }

    Auth::login($user);
    session()->regenerate();

    return redirect()->route('api.test');
});

Route::get('/auto-login/owner', function () {
    $user = \App\Models\User::where('email', 'owner@workspace.com')->first();
    if ($user) {
        Auth::login($user);
        session()->regenerate();
        return redirect()->route('api.test')->with('success', 'Logged in as Owner');
    }
    return redirect('/')->with('error', 'Owner user not found');
});

Route::get('/auto-login/admin', function () {
    $user = \App\Models\User::where('email', 'admin@workspace.com')->first();
    if ($user) {
        Auth::login($user);
        session()->regenerate();
        return redirect('/')->with('success', 'Logged in as Admin');
    }
    return redirect('/')->with('error', 'Admin user not found');
});// Login routes - Use new login.blade.php with app.html UI
Route::get('/login', function () {
    return view('login');
})->name('login');

// Legacy POST /login route - not used anymore (app.html uses API directly)
// Kept for backward compatibility if needed
Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect('/app.html'); // Changed from profile.show to app.html
    }

    return back()->withErrors(['email' => 'Invalid credentials']);
});

// Language switch route - TẠM BỎ
// Route::post('/language/switch', [LanguageController::class, 'switch'])
//    ->name('language.switch');

// Profile routes - client-side rendered with API
Route::view('/profile', 'profile.show')->name('profile.show');
// Edit profile routes (for future use)
// Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
// Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

// Logout - merged from both branches
Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');
