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
        'password_hash', // Ẩn password_hash thay vì password
        'remember_token',
    ];

    /**
     * Định dạng dữ liệu
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_hash' => 'hashed', 
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    /**
     * 5. CẤU HÌNH QUAN TRỌNG: Đổi tên cột password
     * Mặc định Laravel tìm cột 'password'. Database của bạn là 'password_hash'.
     * Hàm này báo cho Laravel biết phải lấy mật khẩu ở đâu.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    // Mutator for name
    public function setNameAttribute($value)
    {
        $this->attributes['full_name'] = $value;
    }

    // Accessor for phone (compatibility)
    public function getPhoneAttribute()
    {
        return $this->phone_number;
    }

    // Mutator for phone
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone_number'] = $value;
    }

    // Accessor for avatar (compatibility)
    public function getAvatarAttribute()
    {
        return $this->profile_avatar_url;
    }

    // Mutator for avatar
    public function setAvatarAttribute($value)
    {
        $this->attributes['profile_avatar_url'] = $value;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is moderator
     */
    public function isModerator()
    {
        return $this->hasRole('moderator');
    }



    public function bookings()
    {
        return $this->hasMany('App\Models\Booking');
    }

    public function conversations()
    {
        return $this->belongsToMany('App\Models\Conversation', 'conversation_participants');
    }

    public function messages()
    {
        return $this->hasMany('App\Models\Message', 'sender_id');
    }

    public function notifications()
    {
        return $this->hasMany('App\Models\Notification');
    }

    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', 'user_roles');
    }

    public function venue_managers()
    {
        return $this->belongsToMany('App\Models\Venue', 'venue_managers');
    }

    public function venues()
    {
        return $this->hasMany('App\Models\Venue', 'owner_id');
    }
}