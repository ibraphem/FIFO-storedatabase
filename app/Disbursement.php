<?php

namespace App;
use App\Item;
use App\Department;
use App\Supplier;

use Illuminate\Database\Eloquent\Model;

class Disbursement extends Model
{
    public function item()
    {
        return $this->belongsTo('App\Item');
    }

    public function department()
    {
        return $this->belongsTo('App\Department');
    }
    public function supplier()
    {
        return $this->belongsTo('App\Supplier');
    }
}
