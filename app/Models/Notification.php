<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    protected $table = 'notifications';

    // Bảng chỉ có created_at, không có updated_at
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'message',
        'type',
        'is_read',
        'related_url',
        'created_at',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
