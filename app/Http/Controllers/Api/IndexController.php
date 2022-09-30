<?php

namespace App\Http\Controllers\Api;

use DB;
use Auth;
use Validator;
use App\Company;
use App\Product;
use App\Category;
use Illuminate\Http\Request;

class IndexController extends Controller
{
 

    public function homepage(Request $request) {
        $now = \Carbon\Carbon::now();
        $lat  = $request->lat;
        $long = $request->long;
        $ispaymenton = $this->ispaymenton();

        $nearbyCompanies = Company::
                           select("*",
                           DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
                           * cos(radians(companies.lat)) 
                           * cos(radians(companies.long) - radians(" . $long . ")) 
                           + sin(radians(". $lat .")) 
                           * sin(radians(companies.lat))) AS distance"))
                           ->where('status',0)
                           ->orderby("distance",'asc')
                           ->having("distance","<=",500);
                            
        if($ispaymenton){
          $nearbyCompanies = $nearbyCompanies->whereDate('expiry_date', '>', $now->toDateString());
        }

        $nearbyCompanies = $nearbyCompanies->get();
                            
                                                                      // use($allowedRoles)
        $categories = Category::with(['companies' => function ($query) use($now,$ispaymenton) {
                      if($ispaymenton){
                        $query->with(['products'])
                              ->select('companies.id','companies.company_name','companies.image','companies.category_id')
                              ->whereDate('expiry_date', '>', $now->toDateString())
                              ->where('companies.status',0)
                              ->limit(50)->orderBy('id','desc');
                        }else{
                          $query->with(['products'])
                              ->select('companies.id','companies.company_name','companies.image','companies.category_id')
                              ->where('companies.status',0)
                              ->limit(50)->orderBy('id','desc');
                        }

                      }])->get();


            $lfcompanies = Company::limit(50)->orderBy('id','desc')->where('status',0);
            if($ispaymenton) {
              $lfcompanies = $lfcompanies->whereDate('expiry_date', '>', $now->toDateString());
            }

            $lfcompanies = $lfcompanies->get();



            $lfc = new  Category();
            $lfc->id   = '0';
            $lfc->name = '50 newest companies';
            $lfc->image = 'category/50fnc1.png';
            $lfc->created_at =  null;
            $lfc->updated_at    =  null;
            $lfc->companies =  $lfcompanies;
            $categories->prepend($lfc);

            if($ispaymenton) {
              $products = Product::limit(50)->orderBy('id','desc')
                          ->whereHas('company', function ($query) use($now) {
                            $query->whereDate('expiry_date', '>', $now->toDateString());
                        })
                        ->get();
            }else{
              $products = Product::limit(50)->orderBy('id','desc')->get();
            }


        $totalcustomers = \App\Customer::count();
        $user = Auth::user();
        $totalunreadnotification = 0;
        if($user){
            $totalunreadnotification = \App\Notification::where('user_id',$user->id)->where('isread','0')->count();
        }
        return response()->json(['result' => 1, "nearbyCompanies" => $nearbyCompanies, "categories" => $categories,'products'=>$products, 'totalcustomers' => $totalcustomers, 'totalunreadnotification' => $totalunreadnotification]);
    } 

    public function neabycompanies(Request $request) {

      $lat = $request->lat;
      $long = $request->long;
      $now = \Carbon\Carbon::now();
      $ispaymenton = $this->ispaymenton();
      $maxdistance = 500;
      if($request->unit == 'miles') {
          $maxdistance =  805;
      }
      $nearbyCompanies = Company::
                         select("*",
                         DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
                         * cos(radians(companies.lat)) 
                         * cos(radians(companies.long) - radians(" . $long . ")) 
                         + sin(radians(". $lat .")) 
                         * sin(radians(companies.lat))) AS distance"))
                         ->where('status',0)
                         ->orderby("distance",'asc')
                         ->having("distance","<=",$maxdistance);
      if($ispaymenton){
        $nearbyCompanies = $nearbyCompanies->whereDate('expiry_date', '>', $now->toDateString());
      }

      $nearbyCompanies = $nearbyCompanies->get();
                         


      return response()->json(['result' => 1, "nearbyCompanies" => $nearbyCompanies]);
    }

    public function getmoreproducts(Request $request) {
        $companies = Company::limit(50)->whereDate('expiry_date', '>', $now->toDateString())->orderBy('id','desc');

        if( $request->category_id) {
            $companies = $companies->where('category_id',$request->category_id);
        }

        if( $request->last_id) {
            $companies = $companies->where('id','>',$request->last_id);
        }
        $companies = $companies->get();
        $ismore = ($product->count() < 10) ?0:1;
        return response()->json(['result' => 1, "products" => $companies]);
    }


     public function getcompanydetail(Request $request) {
        $company = Company::with(['activeoffers.company','products.company'])->find($request->id);

        if($company){
          $user = Auth::user();
          // echo  $user->id;die;
          $company->is_favourite = 0;
          if($user){
             $fav = \App\Favourite::where(['user_id' => $user->id,'company_id' => $company->id])->first();

             if($fav){
                  $company->is_favourite = 1;
             }
          }
 
          $company->is_customer = 0;
          if($user){
             $cus = \App\Customer::where(['user_id' => $user->id,'company_id' => $company->id])->first();
             if($cus){
                  $company->is_customer = 1;
             }
          }
          
          $active = 1;
          if($company->expiry_date && $company->expiry_date > date('Y-m-d')) {
              $active = 0;
          }
          $company->is_expiry = $active;

          return response()->json(['result' => 1, "company" => $company]);
        }
     }

    public function search(Request $request) {
        $now = \Carbon\Carbon::now();
        $ispaymenton = $this->ispaymenton();
        $compnies = Company::limit(20)->offset(($request->page-1)*20)->where('status',0)->orderby("company_name","desc");

        if($ispaymenton) {
          $compnies = $compnies->whereDate('expiry_date', '>', $now->toDateString());
        }

        if($request->company_name) {
            $compnies->where('company_name', 'LIKE', '%' . $request->company_name . '%');
        } 

        if($request->city) {
            $compnies->where('company_name', 'LIKE', $request->city );
        } 
        if($request->postcode) {
            $compnies->where('postcode', $request->postcode);
        }

        $compnies = $compnies->get();
        
        return response()->json(['result' => 1, "companies" => $compnies ]);
    } 


    public function GetCartegoryProducts(Request $request) {
      $now = \Carbon\Carbon::now();
      $ispaymenton = $this->ispaymenton();
      //Last 50 notification thing
      if($request->id == 0) {
          $compnies = Company::limit(50)->where('status',0);

          if($ispaymenton) {
            $compnies = $compnies->whereDate('expiry_date', '>', $now->toDateString());
          }

          if($request->sortby == 'popular' ) {
              $compnies->orderby("totalfavorite","desc")->latest();
          }else if($request->sortby == 'latest' ) {
              $compnies->orderby("created_at","desc")->latest();
          }else{
              $compnies->latest();
          }
          $compnies = $compnies->get();
          return response()->json(['result' => 1, "companies" => $compnies ,'last50' => true ]);
      }else{

          $compnies = Company::where("category_id",$request->id)->where('status',0);

          if($ispaymenton) {
            $compnies = $compnies->whereDate('expiry_date', '>', $now->toDateString());
          }

          if($request->sortby == 'popular' ) {
              $compnies->orderby("totalfavorite","desc");
          }

          if($request->sortby == 'latest' ) {
              $compnies->orderby("created_at","desc");
          }
          $compnies = $compnies->get();
          
          return response()->json(['result' => 1, "companies" => $compnies ]);
      } 
    } 

    protected function ispaymenton() {
         $cs = \DB::table('settings')->where('control_settings','PAYMENT MANDATORY')->first();
         if($cs){
            return $cs->on_off;
         }
         return false;
    }
}