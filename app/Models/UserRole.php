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
    protected $table = 'user_roles';

    public $timestamps = false;

    // Bảng này dùng composite key (user_id, role_id), không auto-increment
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'role_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
