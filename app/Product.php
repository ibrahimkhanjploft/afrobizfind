<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{	
	protected $with = ['images','currency'];

 	public function images() {
        return $this->hasMany('App\ProductImage');
    }

	public function currency() {
        return $this->belongsTo('App\Currency');
    }

    public function company() {
        return $this->belongsTo('App\Company');
    }
    
}
