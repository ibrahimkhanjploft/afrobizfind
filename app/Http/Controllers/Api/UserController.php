<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Validator;
use JWTAuth;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
 
    public function saveUser(Request $request) {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'surname'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => '0', "message" => "Validation error", 'errors' => $validator->errors()->messages()]);
        }

        $u = Auth()->user()->id;

        $user = \App\User::find($u);
        $user->first_name       = $request->first_name;
        $user->surname          = $request->surname;
        $user->username          = $request->username;
        $user->home_number      = $request->home_number;
        $user->address_line_1   = $request->address_line_1;
        $user->city             = $request->city;
        $user->postcode         = $request->postcode;
        $user->mobile_number    = $request->mobile_number;


        /*if ($request->hasFile('image')) {
            $image     = $request->image;
            $extension = $image->getClientOriginalExtension();
            $filename  = 'users/' . md5(rand() . time() . rand()) . "." . $extension;
            \Storage::disk('public_uploads')->put($filename, \File::get($image));
            $user->image = $filename;
        }*/
        $user->save();
        //$user = \App\User::find($user->id);

        return response()->json(['result' => 1, "message" => "User detail updated successfully", "user" => $user]);
    }


     public function removeuser(Request $request) {

         $user =  Auth()->user();
         if($user) {
            $id = $user->id;
            if($user->companies->count()) {
                foreach ($user->companies as $key => $company) {
                    \App\Product::where('company_id',$company->id)->delete();
                    \App\Offer::where('company_id',$company->id)->delete();
                    \App\Favourite::where('company_id',$company->id)->delete();
                    \App\Customer::where('company_id',$company->id)->delete();

                   $company->delete();
                }
            }
            $user->delete();
            return response()->json(['result' => 1, "message" => "User deleted successfully"]);
         }

    }

    public function logout(Request $request) {
        $user = Auth()->user();
        $user->fcmtoken = '';
        $user->save();
        JWTAuth::invalidate(JWTAuth::getToken());
        
        return response()->json(['result' => 1, "message" =>"Logout successfully"]);
    }

    public function usernotifications(Request $request) {
            $user = Auth()->user();
            $offset = 10 * (($request->page??1)-1) ;
            $notifications = \App\Notification::where('user_id',$user->id)
                                       ->limit(10)
                                       ->offset($offset)
                                       ->orderby('id','desc')
                                       ->get();


            $totalnotifications = \App\Notification::where('user_id',$user->id)->count();
            $total_pages = ceil($totalnotifications / 10);
 

            \App\Notification::where('user_id',$user->id)->where('isread','0')->update(array('isread' => 1));

            return response()->json(['result' => 1, "notifications" => $notifications,'totalnotifications' => $totalnotifications, 'total_pages' => $total_pages]);
    }
}
