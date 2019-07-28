<?php

namespace Aleafoodapi;

use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    protected $fillable = [
        'step_number',
        'content',
    ];

    public $timestamps = false;

    public function recipe()
    {
        return $this->belongsTo('Aleafoodapi\Recipe');
    }
}
