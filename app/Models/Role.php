<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property integer $id
 * @property string $role_name
 * @property User[] $users
 */
class Role extends Model
{
    // Bảng roles không có created_at / updated_at
    public $timestamps = false;

    protected $fillable = [
        'role_name',
    ];

    /**
     * Users thuộc role này.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}
