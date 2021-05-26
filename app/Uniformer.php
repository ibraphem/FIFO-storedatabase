<?php

namespace App;
use App\Uniform;

use Illuminate\Database\Eloquent\Model;

class Uniformer extends Model
{
    public function uniform()
    {
        return $this->belongsTo('App\Uniform');
    }
}
