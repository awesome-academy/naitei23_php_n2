<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $amenity_name
 * @property string $icon_url
 * @property Venue[] $venues
 */
class Amenity extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['amenity_name', 'icon_url'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function venues()
    {
        return $this->belongsToMany('App\Models\Venue', 'venue_amenities');
    }
}
