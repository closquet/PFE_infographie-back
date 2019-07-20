<?php

namespace aleafoodapi;

use Illuminate\Database\Eloquent\Model;

class Allergen extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    protected $hidden = array('pivot');

    public function users()
    {
        return $this->belongsToMany('aleafoodapi\User');
    }
}
