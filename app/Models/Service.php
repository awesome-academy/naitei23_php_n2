<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    protected $fillable = [
        'venue_id',
        'name',
        'description',
        'price',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_services')
            ->withPivot(['quantity', 'price_at_booking']);
    }

    public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }
}
