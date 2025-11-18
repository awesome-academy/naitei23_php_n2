<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $space_id
 * @property string $start_time
 * @property string $end_time
 * @property float $total_price
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 * @property BookingService[] $bookingServices
 * @property User $user
 * @property Space $space
 * @property Payment $payment
 */
class Booking extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'space_id', 'start_time', 'end_time', 'total_price', 'status', 'created_at', 'updated_at'];

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
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function space()
    {
        return $this->belongsTo('App\Models\Space');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payment()
    {
        return $this->hasOne('App\Models\Payment', 'booking_id');
    }
}
