<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     * @var array
     */
    protected $fillable = ['user_id', 'role_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }
}
