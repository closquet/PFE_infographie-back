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
        'name'
    ];

    public function allergens()
    {
        return $this->belongsToMany('aleafoodapi\Allergen');
    }

    protected $with = ['allergens:id,name'];
}
