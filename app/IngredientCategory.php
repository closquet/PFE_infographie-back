<?php

namespace Aleafoodapi;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class IngredientCategory extends Model
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

    protected $with = [
        'subCategory:id,name',
    ];

    public function subCategory()
    {
        return $this->hasMany('Aleafoodapi\IngredientSubCat', 'cat_id');
    }
}
