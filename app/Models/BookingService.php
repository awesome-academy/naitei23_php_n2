<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    /**
     * @var array
     */
    protected $fillable = ['quantity', 'price_at_booking'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking()
    {
        return $this->belongsTo('App\Models\Booking');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }
}
