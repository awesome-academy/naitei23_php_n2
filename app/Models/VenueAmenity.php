<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $venue_id
 * @property integer $amenity_id
 * @property Venue $venue
 * @property Amenity $amenity
 */
class VenueAmenity extends Model
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
    public function amenity()
    {
        return $this->belongsTo('App\Models\Amenity');
    }
}
