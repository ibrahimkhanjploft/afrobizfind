<?php

namespace App\Http\Controllers\Api\User;

use Auth;
use Validator;
use App\Offer;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;

class OfferController extends Controller
{

    public function getall(Request $request) {
        $offers = Offer::with('company')->where('company_id',$request->id)->get();
        return response()->json(['result' => 1, "offers" => $offers]);
    }

    public function get(Request $request) {
        $id = $request->id;
        if($id){
            $offer = Offer::find($id);
            if($offer) {

                $offer->company_name = $offer->company->company_name;

                return response()->json(['result' => 1, "offer" => $offer]);
            }
        }
        return response()->json(['result' => 0, 'message' => "Something went wrong"]);
    }
 
 
    public function save(Request $request) {
        $validator=Validator::make($request->all(), [
            'company_id'    => 'required',
            'name'          => 'required',
            'offer_code'    => 'required',
            'offer_details' => 'required',
            'discount'      => 'required',
            'start_date'    => 'required',
            'end_date'      => 'required',
            'customer_only' => 'required',
            // 'mobile_number' => 'required',
            'active'        => 'required',
        ]);
       
        if ($validator->fails()){
            return response()->json(['result' => 0, 'message' => "Validation error",'errors' => $validator->errors()->messages()]);
        }
      
        $user    = Auth()->user();
        $user_id = $user->id;
        if($request->id) {
            $offer = Offer::find($request->id);;
        }else {
            $offer = new Offer();
            $offer->offer_number = $this->generateOfferNumber();
        }

        if(!isset($offer)) {
             return response()->json(['result' => 0, 'message' => "Something went wrong"]);
        }
        $offer->company_id = $request->company_id;
        $offer->name       = $request->name;
        $offer->offer_code = $request->offer_code;
        $offer->offer_details = $request->offer_details;
        $offer->discount = $request->discount;
        $offer->start_date = $request->start_date;
        $offer->end_date = $request->end_date;
        $offer->customer_only = $request->customer_only?1:0;
        // $offer->mobile_number = $request->mobile_number;
        $offer->active = 1;//$request->id?$request->active?1:0:1;
        $offer->save();

        $op = $request->id?'updated':'created';
        return response()->json(['result' => 1,"message" => "Offer $op successfully", "offer" => $offer]);
    }
 

    public function delete(Request $request) {
        $id = $request->id;
        if($id){
            $user_id   = Auth::user()->id;
            $company   = Offer::find($id);
            if($company) {
                $company->delete();
                return response()->json(['result' => 1,  "message" =>"Offer removed successfully"]);
            }
        }
        return response()->json(['result' => 0, 'message' => "Something went wrong"]);
    }

    function generateOfferNumber() {
        $number = 'O'.mt_rand(1000000, 9999999);

        if ($this->OfferNumberExists($number)) {
            return $this->generateOfferNumber();
        }
        return $number;
    }

    function OfferNumberExists($number) {
        return \App\Offer::where('offer_number',$number)->count();
    }

}
