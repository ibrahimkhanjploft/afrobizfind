<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Customer;
use App\Company;
use Validator;

class CustomerController extends Controller
{
    public function getcompanycustomers(Request $request) {
        $company  = Company::with('customers')->find($request->id);
        if($company){
            //dd($company);
            $customers = $company->customers;
            return response()->json(['result' => 1,"users" => $customers ]);
        }
        return response()->json(['result' => 0]);
    }


    public function addtocustomers(Request $request) {
        $user = \Auth::user();
                Customer::updateOrCreate(
                    ['company_id' => $request->id, 'user_id' => $user->id, 'mobileallowed' => $request->mobileallowed?1:0],
                    ['company_id' => $request->id, 'user_id' => $user->id]
                );
        return response()->json(['result' => 1,"message" => "Added as customer" ]);
    }

    public function removecustomer(Request $request) {
        $user = \Auth::user();
        $cus = Customer::where(
                    ['company_id' => $request->id, 'user_id' => $user->id] 
                )->first();
        if($cus) {
            $cus->delete();
        }
        return response()->json(['result' => 1,"message" => "Removed from customer" ]);
    } 
    
}