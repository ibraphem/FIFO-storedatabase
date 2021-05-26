<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function itemPurchase()
    {
        return $this->hasManyThrough('App\Purchase', 'App\Item', 'category_id', 'item_id', 'id', 'id');
    }
    public function item()
    {
        return $this->hasMany('App\Item');
    }
}
