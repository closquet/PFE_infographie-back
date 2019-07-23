<?php

namespace aleafoodapi;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'sub_cat_id',
        'thumbnail',
    ];

    protected $with = [
        'allergens:id,name',
    ];

    public function allergens()
    {
        return $this->belongsToMany('aleafoodapi\Allergen');
    }

    public function disliked_by_users()
    {
        return $this->belongsToMany('aleafoodapi\User', 'user_dislikes_ingredient');
    }

    public function subCategory()
    {
        return $this->belongsTo('aleafoodapi\IngredientSubCat');
    }
}
