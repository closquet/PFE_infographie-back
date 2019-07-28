<?php

namespace Aleafoodapi;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, Sluggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'description',
        'allergens',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pivot',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
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

    public function allergens()
    {
        return $this->belongsToMany('Aleafoodapi\Allergen');
    }


    public function disliked_ingredients()
    {
        return $this->belongsToMany('Aleafoodapi\Ingredient', 'user_dislikes_ingredient');
    }


    public function recipes()
    {
        return $this->hasMany('Aleafoodapi\Recipe');
    }


    public function liked_recipes()
    {
        return $this->belongsToMany('Aleafoodapi\Recipe', 'user_like_recipe');
    }
}
