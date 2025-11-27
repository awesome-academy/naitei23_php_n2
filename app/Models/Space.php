<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    protected $fillable = [
        'venue_id',
        'space_type_id',
        'name',
        'capacity',
        'price_per_hour',
        'price_per_day',
        'price_per_month',
        'open_hour',
        'close_hour',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function spaceType(): BelongsTo
    {
        return $this->belongsTo(SpaceType::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
