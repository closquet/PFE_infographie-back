<?php

namespace Aleafoodapi;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
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
        'sub_cat_id',
        'thumbnail',
        'allergens',
    ];

    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at',
    ];

    public function allergens()
    {
        return $this->belongsToMany('Aleafoodapi\Allergen');
    }

    public function disliked_by_users()
    {
        return $this->belongsToMany('Aleafoodapi\User', 'user_dislikes_ingredient');
    }

    public function subCategory()
    {
        return $this->belongsTo('Aleafoodapi\IngredientSubCat');
    }

    public function seasons()
    {
        return $this->belongsToMany('Aleafoodapi\Season');
    }

    public function recipes()
    {
        return $this->belongsToMany('Aleafoodapi\Recipe');
    }
}
