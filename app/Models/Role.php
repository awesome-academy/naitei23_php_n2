<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $role_name
 * @property User[] $users
 */
class Role extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['role_name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_roles');
    }
}
