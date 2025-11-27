<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $id
 * @property integer $conversation_id
 * @property integer $sender_id
 * @property string $message_body
 * @property string $created_at
 * @property Conversation $conversation
 * @property User $user
 */
class Message extends Model
{
    protected $table = 'messages';

    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message_body',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
