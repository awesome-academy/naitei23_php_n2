<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    /**
     * @var array
     */
    protected $fillable = ['owner_id', 'name', 'description', 'address', 'city', 'street', 'latitude', 'longitude', 'status', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany('App\Models\Service');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function spaces()
    {
        return $this->hasMany('App\Models\Space');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function amenities()
    {
        return $this->belongsToMany('App\Models\Amenity', 'venue_amenities');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'venue_managers');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'owner_id');
    }
}
