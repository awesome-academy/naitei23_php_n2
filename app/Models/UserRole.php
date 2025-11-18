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
     * @var array
     */
    protected $fillable = [];

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
