<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show()
    {
        $user = Auth::user();

        if (!$user) {
            // Nếu chưa login, tạo user demo hoặc redirect
            return redirect('/auto-login');
        }

        return view('profile.show', compact('user'));
    }

    /**
     * Show the form for editing the profile.
     */
    public function edit()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/auto-login');
        }
        \Log::info('Loading edit view for user: ' . $user -> email);
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/auto-login');
        }

        // Validate dữ liệu
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'locale' => 'nullable|in:en,vi',
        ]);

        // Xử lý upload avatar
        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ nếu có
            if ($user->profile_avatar_url && Storage::disk('public')->exists($user->profile_avatar_url)) {
                Storage::disk('public')->delete($user->profile_avatar_url);
            }

            // Lưu avatar mới
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->profile_avatar_url = $avatarPath;
        }

        // Cập nhật thông tin
        $user->full_name = $validated['full_name'];
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone_number'] ?? $user->phone_number;
        $user->bio = $validated['bio'] ?? $user->bio;

        $user->save();

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully!');
    }
}


