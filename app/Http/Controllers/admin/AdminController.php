<?php
namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController  extends Controller
{     
    public function index(){
        $users   = \App\User::count();
        $company = \App\Company::count();
        $product = \App\Product::count();
        $offers  = \App\Offer::count();
        return view('admin.home',compact('users','company','product','offers'));
    } 
}
