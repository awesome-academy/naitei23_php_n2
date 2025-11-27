<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property string $type_name
 * @property Space[] $spaces
 */
class SpaceType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'type_name',
    ];

    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class);
    }
}
