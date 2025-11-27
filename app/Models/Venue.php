<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property integer $id
 * @property integer $owner_id
 * @property string $name
 * @property string $description
 * @property string $address
 * @property string $city
 * @property string $street
 * @property float $latitude
 * @property float $longitude
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 * @property Service[] $services
 * @property Space[] $spaces
 * @property Amenity[] $amenities
 * @property User[] $users
 * @property User $user
 */
class Venue extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_BLOCKED  = 'blocked';

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'address',
        'city',
        'street',
        'latitude',
        'longitude',
        'status',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Giữ lại nếu ở chỗ khác đang dùng $venue->user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'venue_amenities');
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'venue_managers');
    }
}
