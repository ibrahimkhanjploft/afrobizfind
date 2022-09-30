<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{ 

 	protected $fillable = [
        'company_id', 'user_id',
    ];
    
}
