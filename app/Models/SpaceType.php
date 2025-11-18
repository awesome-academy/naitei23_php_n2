<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $type_name
 * @property Space[] $spaces
 */
class SpaceType extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['type_name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function spaces()
    {
        return $this->hasMany('App\Models\Space');
    }
}
