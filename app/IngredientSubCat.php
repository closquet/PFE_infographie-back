<?php

namespace aleafoodapi;

use Illuminate\Database\Eloquent\Model;

class IngredientSubCat extends Model
{
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

    public function ingredients()
    {
        return $this->hasMany('aleafoodapi\Ingredient');
    }

    public function category()
    {
        return $this->belongsTo('aleafoodapi\IngredientCategory');
    }
}
