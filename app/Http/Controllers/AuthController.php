<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Format user data for API response
     * 
     * @param User $user
     * @return array
     */
    private function formatUserResponse(User $user): array
    {
        // Load roles if not loaded
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'profile_avatar_url' => $user->profile_avatar_url,
            'is_active' => $user->is_active,
            'is_verified' => $user->is_verified,
            'roles' => $user->roles()->pluck('role_name')->toArray(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    /**
     * Register a new user (automatically assigns 'user' role)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|confirmed|min:8',
                'phone_number' => 'nullable|string|max:20',
            ]);

            DB::beginTransaction();

            $user = User::create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'password_hash' => Hash::make($validated['password']),
                'phone_number' => $validated['phone_number'] ?? null,
                'is_active' => true,
                'is_verified' => false,
            ]);

            // Tự động gán role 'user' cho người đăng ký
            $userRole = Role::where('role_name', 'user')->first();
            if ($userRole) {
                $user->roles()->attach($userRole->id);
            }

            DB::commit();

            // Load roles relationship
            $user->load('roles');

            // Tạo token cho user
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công!',
                'data' => [
                    'user' => $this->formatUserResponse($user),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi đăng ký.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user and create token
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thông tin đăng nhập không chính xác.',
                    'errors' => [
                        'email' => ['Thông tin đăng nhập không chính xác.']
                    ],
                ], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản của bạn đã bị vô hiệu hóa.',
                ], 403);
            }

            // Xóa các token cũ (optional - nếu muốn chỉ cho phép 1 thiết bị đăng nhập)
            // $user->tokens()->delete();

            // Load roles relationship
            $user->load('roles');

            // Tạo token mới
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập thành công!',
                'data' => [
                    'user' => $this->formatUserResponse($user),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi đăng nhập.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout user (revoke token)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Xóa token hiện tại
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đăng xuất thành công!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi đăng xuất.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $this->formatUserResponse($user),
            ],
        ], 200);
    }

    /**
     * Generate a random password
     * 
     * @param int $length
     * @return string
     */
    private function generateRandomPassword(int $length = 12): string
    {
        // Đảm bảo mật khẩu có đủ các loại ký tự
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*';
        
        // Lấy ít nhất 1 ký tự từ mỗi loại
        $password = $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        
        // Điền phần còn lại
        $allChars = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Xáo trộn mật khẩu
        return str_shuffle($password);
    }

    /**
     * Reset password for user (only for users with 'user' role)
     * Generates a new password and sends it via email
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
            ]);

            $user = User::where('email', $validated['email'])->first();

            // Kiểm tra user tồn tại
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy tài khoản với email này.',
                ], 404);
            }

            // Kiểm tra user có phải là role 'user' không (không cho admin/moderator reset qua API này)
            $userRoles = $user->roles()->pluck('role_name')->toArray();
            
            if (in_array('admin', $userRoles) || in_array('moderator', $userRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản quản trị viên không thể đặt lại mật khẩu qua chức năng này. Vui lòng liên hệ quản trị hệ thống.',
                ], 403);
            }

            // Kiểm tra tài khoản có active không
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ hỗ trợ.',
                ], 403);
            }

            // Tạo mật khẩu mới
            $newPassword = $this->generateRandomPassword(12);

            DB::beginTransaction();

            // Cập nhật mật khẩu
            $user->password_hash = Hash::make($newPassword);
            $user->save();

            // Xóa tất cả token cũ (bắt buộc đăng nhập lại với mật khẩu mới)
            $user->tokens()->delete();

            // Gửi email
            Mail::to($user->email)->send(new PasswordResetMail($user->full_name, $newPassword));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mật khẩu mới đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi đặt lại mật khẩu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
