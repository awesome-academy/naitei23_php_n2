<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $venue_id
 * @property string $name
 * @property float $price
 * @property string $description
 * @property BookingService[] $bookingServices
 * @property Venue $venue
 */
class Service extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['venue_id', 'name', 'price', 'description'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookingServices()
    {
        return $this->hasMany('App\Models\BookingService');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function venue()
    {
        return $this->belongsTo('App\Models\Venue');
    }
}
