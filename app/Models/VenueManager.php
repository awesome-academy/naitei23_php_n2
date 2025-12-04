<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $venue_id
 * @property integer $user_id
 * @property Venue $venue
 * @property User $user
 */
class VenueManager extends Model
{
    protected $table = 'venue_managers';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'venue_id',
        'user_id',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
