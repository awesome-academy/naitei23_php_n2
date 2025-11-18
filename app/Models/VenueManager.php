<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $venue_id
 * @property integer $user_id
 * @property Venue $venue
 * @property User $user
 */
class VenueManager extends Model
{
    /**
     * @var array
     */
    protected $fillable = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function venue()
    {
        return $this->belongsTo('App\Models\Venue');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
