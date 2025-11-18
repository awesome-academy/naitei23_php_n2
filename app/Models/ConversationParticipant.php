<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $conversation_id
 * @property integer $user_id
 * @property Conversation $conversation
 * @property User $user
 */
class ConversationParticipant extends Model
{
    /**
     * @var array
     */
    protected $fillable = [];

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
        return $this->belongsTo('App\Models\User');
    }
}
