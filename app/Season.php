<?php

namespace Aleafoodapi;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
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
    ];

    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at',
    ];

    public function ingredients()
    {
        return $this->belongsToMany('Aleafoodapi\Ingredients');
    }
}
