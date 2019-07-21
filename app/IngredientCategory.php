<?php

namespace aleafoodapi;

use Illuminate\Database\Eloquent\Model;

class IngredientCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

//    public function ingredients()
//    {
//        return $this->belongsToMany('aleafoodapi\Ingredient', 'ingredient_ingredient_category');
//    }
}
