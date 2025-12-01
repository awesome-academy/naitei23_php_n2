<?php

namespace App\Models;

// 1. Import các thư viện cần thiết cho xác thực (Auth)
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * 2. Copy DocBlock để IDE gợi ý code tốt hơn
 * @property integer $id
 * @property string $full_name
 * @property string $email
 * @property string $password_hash
 * @property string $phone_number
 * @property string $profile_avatar_url
 * @property boolean $is_active
 * @property boolean $is_verified
 * @property string $verification_token
 * @property string $password_reset_token
 * @property string $created_at
 * @property string $updated_at
 * @property Booking[] $bookings
 * @property Conversation[] $conversations
 * @property Message[] $messages
 * @property Notification[] $notifications
 * @property Role[] $roles
 * @property Venue[] $venues
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 4. Cập nhật $fillable theo đúng database
     */
    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'phone_number',
        'profile_avatar_url',
        'is_active',
        'is_verified',
        'verification_token',
        'password_reset_token',
        'created_at',
        'updated_at'
    ];

    /**
     * Những trường cần ẩn đi khi trả về JSON (API)
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
        'verification_token',
        'password_reset_token',
    ];

    /**
     * Định dạng dữ liệu
     */
    protected $casts = [
        'is_active'   => 'boolean',
        'is_verified' => 'boolean',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Laravel mặc định dùng field 'password', nhưng DB đang dùng 'password_hash'.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Các booking mà user này đã tạo.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Roles mà user đang có (admin, moderator, venue_owner, customer...).
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
<<<<<<< HEAD
     * Các venue mà user này là manager (qua bảng venue_managers).
     */
    public function managedVenues()
=======
     * Check if user has a specific role
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('role_name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles
     *
     * @param array $roleNames
     * @return bool
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('role_name', $roleNames)->exists();
    }

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is moderator
     *
     * @return bool
     */
    public function isModerator(): bool
    {
        return $this->hasRole('moderator');
    }

    /**
     * Check if user is regular user
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    /**
     * Assign a role to user
     *
     * @param string $roleName
     * @return void
     */
    public function assignRole(string $roleName): void
    {
        $role = \App\Models\Role::where('role_name', $roleName)->first();
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Remove a role from user
     *
     * @param string $roleName
     * @return void
     */
    public function removeRole(string $roleName): void
    {
        $role = \App\Models\Role::where('role_name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    public function venue_managers()
>>>>>>> origin/API/auth
    {
        return $this->belongsToMany(Venue::class, 'venue_managers');
    }

    /**
     * Các venue mà user này là owner.
     */
    public function venues()
    {
        return $this->hasMany(Venue::class, 'owner_id');
    }

    /**
     * Kiểm tra user có phải admin không.
     */
    public function isAdmin(): bool
    {
        return $this->roles()
            ->where('role_name', 'admin')
            ->exists();
    }
}
