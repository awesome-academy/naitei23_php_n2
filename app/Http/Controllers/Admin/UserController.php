<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Format user data for API response
     * 
     * @param User $user
     * @return array
     */
    private function formatUserResponse(User $user): array
    {
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
     * Get list of all users (Admin only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $role = $request->input('role');

            $query = User::with('roles');

            // Search by name or email
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('role_name', $role);
                });
            }

            $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users->map(fn($user) => $this->formatUserResponse($user)),
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách người dùng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific user (Admin only)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = User::with('roles')->find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $this->formatUserResponse($user),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new user with specific role (Admin only)
     * Used to create moderator or admin accounts
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'phone_number' => 'nullable|string|max:20',
                'role' => 'required|string|in:user,moderator,admin',
                'is_active' => 'nullable|boolean',
                'is_verified' => 'nullable|boolean',
            ]);

            DB::beginTransaction();

            $user = User::create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'password_hash' => Hash::make($validated['password']),
                'phone_number' => $validated['phone_number'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'is_verified' => $validated['is_verified'] ?? true, // Admin created accounts are verified by default
            ]);

            // Gán role cho user
            $role = Role::where('role_name', $validated['role'])->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }

            DB::commit();

            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'Tạo tài khoản thành công!',
                'data' => [
                    'user' => $this->formatUserResponse($user),
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
                'message' => 'Đã xảy ra lỗi khi tạo tài khoản.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user information (Admin only)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 404);
            }

            $validated = $request->validate([
                'full_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|string|min:8',
                'phone_number' => 'nullable|string|max:20',
                'is_active' => 'sometimes|boolean',
                'is_verified' => 'sometimes|boolean',
            ]);

            DB::beginTransaction();

            if (isset($validated['full_name'])) {
                $user->full_name = $validated['full_name'];
            }
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }
            if (isset($validated['password'])) {
                $user->password_hash = Hash::make($validated['password']);
            }
            if (isset($validated['phone_number'])) {
                $user->phone_number = $validated['phone_number'];
            }
            if (isset($validated['is_active'])) {
                $user->is_active = $validated['is_active'];
            }
            if (isset($validated['is_verified'])) {
                $user->is_verified = $validated['is_verified'];
            }

            $user->save();

            DB::commit();

            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin thành công!',
                'data' => [
                    'user' => $this->formatUserResponse($user),
                ],
            ], 200);
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
                'message' => 'Đã xảy ra lỗi khi cập nhật.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user's role (Admin only)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateRole(Request $request, int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 404);
            }

            $validated = $request->validate([
                'role' => 'required|string|in:user,moderator,admin',
            ]);

            // Prevent admin from changing their own role
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không thể thay đổi role của chính mình.',
                ], 403);
            }

            DB::beginTransaction();

            // Remove all current roles
            $user->roles()->detach();

            // Assign new role
            $role = Role::where('role_name', $validated['role'])->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }

            DB::commit();

            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật role thành công!',
                'data' => [
                    'user' => $this->formatUserResponse($user),
                ],
            ], 200);
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
                'message' => 'Đã xảy ra lỗi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a user (Admin only)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 404);
            }

            // Prevent admin from deleting themselves
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không thể xóa tài khoản của chính mình.',
                ], 403);
            }

            DB::beginTransaction();

            // Remove all roles
            $user->roles()->detach();

            // Revoke all tokens
            $user->tokens()->delete();

            // Delete user
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa tài khoản thành công!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xóa tài khoản.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle user active status (Admin only)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function toggleActive(int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 404);
            }

            // Prevent admin from deactivating themselves
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không thể vô hiệu hóa tài khoản của chính mình.',
                ], 403);
            }

            $user->is_active = !$user->is_active;
            $user->save();

            // If deactivating, revoke all tokens
            if (!$user->is_active) {
                $user->tokens()->delete();
            }

            $user->load('roles');

            $status = $user->is_active ? 'kích hoạt' : 'vô hiệu hóa';

            return response()->json([
                'success' => true,
                'message' => "Đã {$status} tài khoản thành công!",
                'data' => [
                    'user' => $this->formatUserResponse($user),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
