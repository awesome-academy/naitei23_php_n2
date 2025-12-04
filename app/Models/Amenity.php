<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property integer $id
 * @property string $amenity_name
 * @property string $icon_url
 * @property Venue[] $venues
 */
class Amenity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'amenity_name',
        'icon_url',
    ];

    public function venues(): BelongsToMany
    {
        return $this->belongsToMany(Venue::class, 'venue_amenities');
    }
}
