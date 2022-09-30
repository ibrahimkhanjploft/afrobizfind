<?php

namespace App;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $with = ['images'];
    
    protected $appends = ['imagepath'];
 	public function getImagepathAttribute($value) {
        if($this->image) {
            return  url("storage/".$this->image);
        }
    }

    public function activeoffers(){
    	$today = Carbon::today();
    	 return $this->hasMany('App\Offer')
    	 			 ->whereDate('start_date', '<=', $today->format('Y-m-d'))
                     ->whereDate('end_date', '>=', $today->format('Y-m-d'))->where('active',1);
    }

    public function products() {
        return $this->hasMany('App\Product');
    }

    public function images() {
        return $this->hasMany('App\CompanyImage');
    }

    public function offers() {
        return $this->hasMany('App\Offer');
    }

    public function category() {
        return $this->belongsTo('App\Category');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function customers() {
        return $this->belongsToMany(User::class, 'customers')->select("*","customers.mobileallowed");
    }

}