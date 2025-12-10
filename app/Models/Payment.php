<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property integer $id
 * @property integer $booking_id
 * @property float $amount
 * @property string $payment_method
 * @property string $transaction_id
 * @property string $transaction_status
 * @property string $paid_at
 * @property array $meta
 * @property string $created_at
 * @property string $updated_at
 * @property Booking $booking
 */
class Payment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED  = 'failed';

    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'transaction_id',
        'transaction_status',
        'paid_at',
        'meta',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'paid_at'     => 'datetime',
        'meta'        => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
