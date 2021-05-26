<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public function category()
    {
        return $this->hasMany('App\Category', 'id', 'category_id');
    }

    public function purchase()
    {
        return $this->hasMany('App\Purchase', 'item_id', 'id');
    }
}
