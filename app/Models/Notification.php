<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $message
 * @property string $type
 * @property boolean $is_read
 * @property string $related_url
 * @property string $created_at
 * @property User $user
 */
class Notification extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'message', 'type', 'is_read', 'related_url', 'created_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
