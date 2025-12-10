<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

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
    use HasFactory;

    // Trạng thái booking (ENUM trong DB)
    public const STATUS_PENDING_CONFIRMATION = 'pending_confirmation';
    public const STATUS_AWAITING_PAYMENT     = 'awaiting_payment';
    public const STATUS_CONFIRMED            = 'confirmed';
    public const STATUS_PAID                 = 'paid';
    public const STATUS_CANCELLED            = 'cancelled';
    public const STATUS_COMPLETED            = 'completed';

    protected $fillable = [
        'user_id',
        'space_id',
        'start_time',
        'end_time',
        'total_price',
        'status',
        'note',
        'paid_at',
    ];

    protected $casts = [
        'start_time'  => 'datetime',
        'end_time'    => 'datetime',
        'total_price' => 'decimal:2',
        'paid_at'     => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Người dùng tạo booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Space được đặt.
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Payment gắn với booking (1–1).
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * All payments for this booking.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Các service được chọn trong booking (nhiều-nhiều).
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'booking_services')
            ->withPivot(['quantity', 'price_at_booking']);
    }

    /**
     * Nếu muốn dùng pivot model BookingService riêng.
     */
    public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    /**
     * Scope để lọc bookings của user cụ thể.
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope để lọc bookings của owner/manager.
     */
    public function scopeForOwner($query, User $user)
    {
        return $query->whereHas('space.venue', function ($q) use ($user) {
            $q->where('owner_id', $user->id)
              ->orWhereHas('managers', function ($q2) use ($user) {
                  $q2->where('user_id', $user->id);
              });
        });
    }
}
