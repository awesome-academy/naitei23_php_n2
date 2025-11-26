<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $booking_id
 * @property integer $service_id
 * @property integer $quantity
 * @property float $price_at_booking
 * @property Booking $booking
 * @property Service $service
 */
class BookingService extends Model
{
    protected $table = 'booking_services';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'booking_id',
        'service_id',
        'quantity',
        'price_at_booking',
    ];

    protected $casts = [
        'quantity'         => 'integer',
        'price_at_booking' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
