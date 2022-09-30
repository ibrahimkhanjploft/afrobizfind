<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Validator;

class OtherController extends Controller
{
    public function getallcategories() {
        $categories = \App\Category::get();
        //custom company
        $fnc = new \App\Category();
        $fnc->id = 0;
        $fnc->name = '50 newest companies';
        $fnc->image = 'category/50fnc1.png';
        $fnc->created_at =  null;
        $fnc->updated_at    =  null;
        $categories->prepend($fnc);
        return response()->json(['result' => 1,"categories" => $categories ]);
    }
 
    public function getallcurrencies() {
        $currencies = \App\Currency::get();
        return response()->json(['result' => 1,"currencies" => $currencies ]);
    }
 

    public function getfavouriteCompanies(Request $request) {
        $favoriteCompanies    = Auth()->user()->favoriteCompanies;
        return response()->json(['result' => 1,"categories" => $categories ]);
    }

    public function getpaymentstatus(Request $request) {
        $cs = \DB::table('settings')->where('control_settings','PAYMENT MANDATORY')->first();
        return response()->json(['result' => 1,"data" => $cs ]);
    }
    
    public function getversionhistiry() {
        $versions = \App\Version::latest()->get();
        return response()->json(['result' => 1,"versions" => $versions ]);
    }

   /*  public function getStatuses() {
        $statuses = \App\Status::select("id","title","description")->get();
       return response()->json(['result' => 1,"statuses" => $statuses ]);
    }

    public function getReasons() {
        $reasons = \App\Reason::select("id","title")->get();
       return response()->json(['result' => 1,"reasons" => $reasons ]);
    }
    
    public function getFaq() {
        $faqs = \App\Faq::select("question","answer")->get();
       return response()->json(['result' => 1,"faqs" => $faqs ]);
    }

    public function getAlluserNotifications() {
        $user = \Auth::user();
        return response()->json(['result' => 1,"notifications" => $user->notifications ]);
    }
*/
    public function contactus(Request $request) {
        $validator=Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'message' => 'required',
            ]);
      
        if ($validator->fails()){
              return response()->json(['result' => '0',"message"=>"Validation error",'errors' => $validator->errors()->messages()]);
        }

        $contact = $request->only('name','email','message');


        /*$contact = new  \App\Contactus();
        $contact->name  = $request->name;
        $contact->email = $request->email;
        $contact->message   = $request->message;
        $contact->save();*/
        
        \Mail::send('emails.contactus',['contact'=>$contact] , function($message) {
           $message->to('inquiry@afrobizfind.com', 'Afrobiz find')
           ->subject('Contact us message on Afrobizfind ');
        });
        return response()->json(['result' => 1,"message" => "Thank you for the message, We will contact you back soon"]);
    }
    
}
