<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    use HasFactory;

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

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'space_amenities');
    }

    /**
     * Get upcoming bookings for this space (next 30 days).
     */
    public function upcomingBookings(): HasMany
    {
        $now = now();
        $end = now()->addDays(30);

        return $this->hasMany(Booking::class)
            ->where('start_time', '>=', $now)
            ->where('start_time', '<=', $end)
            ->whereIn('status', [
                Booking::STATUS_PENDING_CONFIRMATION,
                Booking::STATUS_AWAITING_PAYMENT,
                Booking::STATUS_CONFIRMED,
            ])
            ->orderBy('start_time');
    }

    /**
     * Scope to filter spaces available between given time range.
     * Excludes spaces with confirmed/paid bookings that overlap.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailableBetween($query, \Carbon\Carbon $start, \Carbon\Carbon $end)
    {
        return $query->whereDoesntHave('bookings', function ($q) use ($start, $end) {
            $q->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_PAID,
            ])->where(function ($q2) use ($start, $end) {
                // Overlap condition: booking overlaps with search range
                $q2->where('start_time', '<', $end)
                   ->where('end_time', '>', $start);
            });
        });
    }

    /**
     * Scope: lọc spaces thuộc venues của owner hoặc venues mà user là manager
     */
    public function scopeForOwnerOrManager($query, $userId)
    {
        return $query->whereHas('venue', function ($q) use ($userId) {
            $q->where('owner_id', $userId)
              ->orWhereHas('managers', function ($q2) use ($userId) {
                  $q2->where('user_id', $userId);
              });
        });
    }
}
