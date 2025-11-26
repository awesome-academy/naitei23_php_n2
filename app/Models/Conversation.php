<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property User[] $users
 * @property Message[] $messages
 */
class Conversation extends Model
{
    protected $fillable = [
        'name',
    ];

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
