<?php

namespace Aleafoodapi;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use Sluggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'preparation_time',
        'cooking_time',
        'user_id',
        'thumbnail',
        'ingredients',
    ];

    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at',
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function ingredients()
    {
        return $this->belongsToMany('Aleafoodapi\Ingredient');
    }

    public function user()
    {
        return $this->belongsTo('Aleafoodapi\User');
    }

    public function tags()
    {
        return $this->belongsToMany('Aleafoodapi\Tag');
    }

    public function liked_by_users()
    {
        return $this->belongsToMany('Aleafoodapi\User', 'user_like_recipe');
    }
}
