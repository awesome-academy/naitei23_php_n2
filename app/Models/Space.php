<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $venue_id
 * @property integer $space_type_id
 * @property string $name
 * @property integer $capacity
 * @property float $price_per_hour
 * @property float $price_per_day
 * @property float $price_per_month
 * @property string $open_hour
 * @property string $close_hour
 * @property string $created_at
 * @property string $updated_at
 * @property Booking[] $bookings
 * @property Venue $venue
 * @property SpaceType $spaceType
 */
class Space extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['venue_id', 'space_type_id', 'name', 'capacity', 'price_per_hour', 'price_per_day', 'price_per_month', 'open_hour', 'close_hour', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany('App\Models\Booking');
    }

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
    public function spaceType()
    {
        return $this->belongsTo('App\Models\SpaceType');
    }
}
