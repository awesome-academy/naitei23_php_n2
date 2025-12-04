<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $user_id
 * @property integer $role_id
 * @property User $user
 * @property Role $role
 */
class UserRole extends Model
{
    /**
     * Tên bảng
     */
    protected $table = 'user_roles';

    /**
     * Tắt timestamps vì bảng user_roles không có created_at và updated_at
     */
    public $timestamps = false;

    /**
     * Bảng này dùng composite key (user_id, role_id), không auto-increment
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'role_id'];

<<<<<<< HEAD
=======
    // Bảng này dùng composite key (user_id, role_id), không auto-increment
    public $incrementing = false;

>>>>>>> master
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
