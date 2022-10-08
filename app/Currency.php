<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    //public $timestamps = false;
    
    protected $fillable = [
        'name', 'currency_code','country_name','country_price','currency_sign','price_in_uk'
    ];

    public function scopeGetCount($query,$search ='') {
        if($search!=''){
            return $query->where('name','LIKE',"%{$search}%")->count();
        }
        return $query->count();
    }

    public function scopeGetData($query, $filters = array()) {
        $orderby           = (@$filters['orderby'])?$filters['orderby']:'created_at';
        $dir               = (@$filters['dir'])?$filters['dir']:'desc';
        $search            =  @$filters['search'];
        $start             =  @$filters['start'];
        $limit             =  @$filters['limit'];
        $status            =  @$filters['status']; 
        $select            =  @$filters['select'];
         
        $data =  $query->orderBy($orderby, $dir );
        
        if($search   != ''){ $data = $data->where('name','LIKE',"%{$search}%"); }     
        if($select   != ''){ $data = $data->select($select); }
        if($start    != ''){ $data = $data->offset($start)->limit($limit); }
        return $data->get();
    }

}
