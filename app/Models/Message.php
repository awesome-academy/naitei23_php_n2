<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    /**
     * @var array
     */
    protected $fillable = ['conversation_id', 'sender_id', 'message_body', 'created_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function conversation()
    {
        return $this->belongsTo('App\Models\Conversation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'sender_id');
    }
}
