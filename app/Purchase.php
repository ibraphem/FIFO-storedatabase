<?php

namespace App;

use App\Item;
use App\Supplier;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    public function item()
    {
        return $this->belongsTo('App\Item');
    }
    public function supplier()
    {
        return $this->belongsTo('App\Supplier');
    }
}
