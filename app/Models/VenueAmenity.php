<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $venue_id
 * @property integer $amenity_id
 * @property Venue $venue
 * @property Amenity $amenity
 */
class VenueAmenity extends Model
{
    protected $table = 'venue_amenities';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'venue_id',
        'amenity_id',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function amenity(): BelongsTo
    {
        return $this->belongsTo(Amenity::class);
    }
}
