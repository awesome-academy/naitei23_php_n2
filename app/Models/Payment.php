<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $booking_id
 * @property float $amount
 * @property string $payment_method
 * @property string $transaction_id
 * @property string $transaction_status
 * @property string $created_at
 * @property string $updated_at
 * @property Booking $booking
 */
class Payment extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['booking_id', 'amount', 'payment_method', 'transaction_id', 'transaction_status', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking()
    {
        return $this->belongsTo('App\Models\Booking');
    }
}
