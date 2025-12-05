<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
// use App\Http\Controllers\LanguageController; // Comment tạm
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
   return view('welcome');
});

// Register routes - from test/api-checklist branch
Route::get('/register', [AuthController::class, 'create']);
Route::post('/register', [AuthController::class, 'store']);

// AUTO-LOGIN ĐỂ TEST (THÊM VÀO) - from view-fix-avatar branch
Route::get('/auto-login', function () {
    $user = \App\Models\User::first();
    
    if (!$user) {
        $user = \App\Models\User::create([
            'full_name' => 'Demo User',
            'email' => 'demo@gmail.com',
            'password_hash' => bcrypt('123456'),
            'phone_number' => '0123456789',
            'is_active' => true,
            'is_verified' => true,
        ]);
    }
    
    Auth::login($user);
    session()->regenerate();
    
    return redirect()->route('profile.show');
});

// Login routes (merge both versions)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);
    
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->route('profile.show');
    }
    
    return back()->withErrors(['email' => 'Invalid credentials']);
});

// Language switch route - TẠM BỎ
// Route::post('/language/switch', [LanguageController::class, 'switch'])
//    ->name('language.switch');

// Profile routes (KHÔNG cần auth middleware để test nhanh)
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

// Admin routes (tạm comment nếu chưa cần)
// Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
//    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
// });

// Logout - merged from both branches
Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');
