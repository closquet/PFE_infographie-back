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

    protected $with = [
        'subCategory:id,name',
    ];

    public function subCategory()
    {
        return $this->hasMany('aleafoodapi\IngredientSubCat', 'cat_id');
    }
}
