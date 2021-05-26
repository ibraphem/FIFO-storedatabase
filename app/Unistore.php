<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unistore extends Model
{
    public function uniform()
    {
        return $this->belongsTo('App\Uniform');
    }
}
