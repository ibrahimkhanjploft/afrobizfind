<?php

namespace App\Http\Controllers\Api\User;

use Auth;
use Validator;
use App\Product;
use App\Company;
use App\ProductImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;

class ProductController extends Controller
{
 

    public function getallcompanyproduct(Request $request) {
        $products = Product::with('company')->where('company_id',$request->id)->get();
        return response()->json(['result' => 1, "products" => $products]);
    }

    public function get(Request $request) {
        $id = $request->id;
        if($id){
            $product = Product::find($id);
            if($product) {
                return response()->json(['result' => 1, "product" => $product]);
            }
        }
        return response()->json(['result' => 0, 'message' => "Something went wrong"]);
    }
 
 
    public function save(Request $request) {
        $validator=Validator::make($request->all(), [
            'company_id'    => 'required',
            'product_name'  => 'required',
            'description'   => 'required',
            'price'         => 'required',
            'images'        => 'required_without:id',
        ]);
      
        if ($validator->fails()){
            return response()->json(['result' => 0, 'message' => "Validation error",'errors' => $validator->errors()->messages()]);
        }
      
        $user    = Auth()->user();
        $user_id = $user->id;
        if($request->id) {
            $product = Product::find($request->id);
        }else {
            $product = new Product();
            $product->product_number = $this->generateProductNumber();
        }

        if(!isset($product)) {
             return response()->json(['result' => 0, 'message' => "Something went wrong"]);
        }
        $product->company_id   = $request->company_id;
        $product->product_name = $request->product_name;
        $product->description  = $request->description;
        $product->price        = $request->price;
        $product->currency_id        = $request->currency_id;
        $product->save();

        $images = [];
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image) {
                $extension = $image->getClientOriginalExtension();
                $filename = '/product/'.md5(rand().time().rand()).".".$extension;
                $images[] = $filename;
               \Storage::disk('public')->put($filename,  \File::get($image));
            }
        }

        if(!empty($images)) {
            foreach ($images as $key => $img) {
                $image = new  ProductImage();
                $image->product_id = $product->id;
                $image->image = $img;
                $image->save();

            }
        }

        if($request->deletedimages) {
            $diary = explode(',', $request->deletedimages);
            if(!empty($diary)){
                foreach ($diary as  $di_id) {
                    $di = ProductImage::find($di_id);
                    if($di){
                        $di->delete();
                    }
                }
            }
        }
        

        $product = Product::find($product->id);

        $op = $request->id?'updated':'created';
        return response()->json(['result' => 1,"message" => "Product $op successfully", "product" => $product]);
    }
 

    public function delete(Request $request) {
        $id = $request->id;
        if($id){
            $user_id = Auth::user()->id;
            $company   = Product::find($id);
            if($company) {
                $company->delete();
                return response()->json(['result' => 1,  "message" =>"Product removed successfully"]);
            }
        }
        return response()->json(['result' => 0, 'message' => "Something went wrong"]);
    }


    function generateProductNumber() {
        $number = 'P'.mt_rand(1000000, 9999999);

        if ($this->ProductNumberExists($number)) {
            return $this->generateProductNumber();
        }
        return $number;
    }

    function ProductNumberExists($number) {
        return \App\Product::where('product_number',$number)->count();
    }
}
