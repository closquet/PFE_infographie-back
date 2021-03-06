<?php

namespace Aleafoodapi;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class IngredientSubCat extends Model
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
        'cat_id',
        'thumbnail',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'cat_id',
    ];

    public function ingredients()
    {
        return $this->hasMany('Aleafoodapi\Ingredient');
    }

    public function category()
    {
        return $this->belongsTo('Aleafoodapi\IngredientCategory', 'cat_id', 'id');
    }
}
