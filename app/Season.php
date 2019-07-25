<?php

namespace aleafoodapi;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    protected $hidden = array('pivot');

    public function ingredients()
    {
        return $this->belongsToMany('aleafoodapi\Ingredients');
    }
}
