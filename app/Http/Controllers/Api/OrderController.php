<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\order;
use App\orderstatus;
use Auth;
use DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $price = DB::table('products')->where('id',$request->productid)->first();

        $mprice=$price->price;
        $finalprice=explode(' ',$mprice);                  

        $orderdata= new order;
        $id = Auth::id();        
        $qty=$request->quantity;
        $total=$finalprice[1]*$qty;      


        $orderdata['productid']=$request->productid;
        $orderdata['userid']=$id;
        $orderdata['price']=$finalprice[1];
        $orderdata['quantity']=$request->quantity;
        $orderdata['totalprice']=$total;
        $orderdata['orderstatus']=$request->orderstatus;
        $orderdata['paymentmethod']=$request->paymentmethod;

        if($orderdata->save())
        {
            return response()->json(['result'=>1,'message'=>'Order created successfully']);
        }
        else
        {
            return response()->json(['error'=>'Something Went Wrong please try again later']);
        }
    } 
    
    public function allorderhistory(Request $request)
    {
        $id = Auth::id();
        $orderhistory = DB::table('orders')
        ->join('users', 'users.id', '=', 'orders.userid')
        ->join('products', 'products.id', '=', 'orders.productid')
        ->join('orderstatus','orderstatus.id','orders.orderstatus')
        ->join('companies','companies.id','products.company_id')
        ->select('products.product_name','users.username','orders.*','companies.company_name','orderstatus.status')
        ->where('orders.userid', '=', $id)
        ->get();
 
        if($orderhistory==true)
        {
            return response()->json(['result'=>1,'orderhistory'=>$orderhistory]);
        }
        else
        {
            return response()->json(['result'=>0,'orderhistory'=>'Something Went wrong Please try Again later.']);
        }       

    }

    public function companyorder(Request $request)
    {        
        $cmpid=$request->companyid;
       
        $companyorder = DB::table('orders')
        ->join('users','users.id','orders.userid')
        ->join('products','products.id','orders.productid')
        ->join('companies','companies.id','products.company_id')
        ->join('orderstatus','orderstatus.id','orders.orderstatus')
        ->select('orders.*','users.username','products.product_name','companies.company_name','orderstatus.status')
        ->where('companies.id','=',$cmpid)->get();

        if($companyorder==true)
        {
            return response()->json(['result'=>1,'companyorder'=>$companyorder]);
        }
        else{
            return response()->json(['result'=>0,'companyorder'=>'Something Went Wrong']);
        }        
    }

    public function singleorder(Request $request)
    {
        $orderid=$request->orderid;
        
        $singleorder = DB::table('orders')        
        ->join('products','products.id','orders.productid')
        ->join('companies','companies.id','products.company_id')
        ->join('orderstatus','orderstatus.id','orders.orderstatus')
        ->select('products.product_name','companies.company_name','orders.*','orderstatus.status')
        ->where('orders.id','=',$orderid)->get();

        if($singleorder==true)
        {
            return response()->json(['result'=>1,'orderdetails'=>$singleorder]);
        }
    }

    // public function updateorder(Request $request)
    // {

    // }

    public function orderstatus()
    {
        $orderstatus=orderstatus::all();

        return response()->json(['result'=>1,'orderstatus'=>$orderstatus]);
    }
}
