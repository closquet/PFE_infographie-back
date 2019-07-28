<?php

namespace Aleafoodapi;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Allergen extends Model
{
    use Sluggable;

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'thumbnail',
    ];

    protected $hidden = array('pivot');

    public function users()
    {
        return $this->belongsToMany('Aleafoodapi\User');
    }

    public function ingredients()
    {
        return $this->belongsToMany('Aleafoodapi\Ingredients');
    }
}
