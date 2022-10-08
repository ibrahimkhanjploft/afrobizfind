<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/  
Route::post('login', 'Api\Auth\LoginController@login');
Route::post('register', 'Api\Auth\LoginController@register');
// Route::post('forgotpassword', 'Api\Auth\ForgotpassController@sendResetLinkEmail');
Route::get('getpaymentstatus', 'Api\OtherController@getpaymentstatus');
Route::post('contactus', 'Api\OtherController@contactus');
Route::get('getversionhistiry', 'Api\OtherController@getversionhistiry');

Route::post('forgotpassword', 'Api\Auth\ForgotpassController@forgotpassword');
Route::post('verifyopt', 'Api\Auth\ForgotpassController@verifyopt');


Route::post('gethomepagedata', 'Api\IndexController@homepage');
Route::post('getcompany', 'Api\CompanyController@getcompany');
Route::post('search', 'Api\IndexController@search');
Route::post('GetCartegoryProducts', 'Api\IndexController@GetCartegoryProducts');
Route::get('getallcurrencies', 'Api\OtherController@getallcurrencies');
Route::get('getallcategories', 'Api\OtherController@getallcategories');
Route::post('getmoreproducts', 'Api\IndexController@getmoreproducts');//Not in use
Route::post('neabycompanies', 'Api\IndexController@neabycompanies');//Not in use


Route::group(['middleware' => 'jwt.auth'], function() { 

    Route::post('payment', 'Api\StripeController@payment')->name('payment');

    Route::post('changepassword', 'Api\Auth\ForgotpassController@changepassword');
    Route::post('getcompanydetail', 'Api\IndexController@getcompanydetail');

    //events
    Route::post('addevent','Api\EventController@store');


    //Order
    Route::post('addorder','Api\OrderController@store');
    Route::get('allorderhistory','Api\OrderController@allorderhistory');  //users multiple companies order
    Route::get('fetchorderstatus','Api\OrderController@orderstatus');  //order status 
    Route::get('companyorder','Api\OrderController@companyorder');  //particular company order individual
    Route::get('singleorder','Api\OrderController@singleorder'); //get the single order details
     //modify order details.
    // Route::post('updateorder','Api\OrderController@updateorder');
    


  	
    /*User Companies CRUD start*/
  	Route::group([ 'prefix' => 'user/company' ], function () {
        Route::get('getall', 'Api\User\CompanyController@getall');
        Route::post('get', 'Api\User\CompanyController@get');
        Route::post('create', 'Api\User\CompanyController@save');
        Route::post('update', 'Api\User\CompanyController@save');
        Route::post('changestatus', 'Api\User\CompanyController@changestatus');
        Route::post('delete', 'Api\User\CompanyController@delete');
        Route::post('pay', 'Api\User\CompanyController@companyPay');

    });

    Route::get('/payment/gettoken', 'Api\PaymentsController@gettoken');
    Route::post('/payment/make', 'Api\PaymentsController@make');

    Route::group([ 'prefix' => 'user/company/product' ], function () {
        Route::post('getall', 'Api\User\ProductController@getallcompanyproduct');
        Route::post('get', 'Api\User\ProductController@get');
        Route::post('create', 'Api\User\ProductController@save');
        Route::post('update', 'Api\User\ProductController@save');
        Route::post('delete', 'Api\User\ProductController@delete');
    });

    Route::group([ 'prefix' => 'user/company/offer' ], function () {
        Route::post('getall', 'Api\User\OfferController@getall');
        Route::post('get', 'Api\User\OfferController@get');
        Route::post('create', 'Api\User\OfferController@save');
        // Route::post('update', 'Api\User\OfferController@save');
        Route::post('delete', 'Api\User\OfferController@delete');
    });
    /*User Companies CRUD END*/

    /*Customer*/
     Route::group([ 'prefix' => 'customer' ], function () {
        Route::get('add', 'Api\CustomerController@add');
        Route::post('remove', 'Api\CustomerController@remove');
    });
 
    /*Front side*/
    /*Route::post('gethomepagedata', 'Api\IndexController@homepage');
    Route::post('getmoreproducts', 'Api\IndexController@getmoreproducts');
    
    Route::post('getcompanydetail', 'Api\IndexController@getcompanydetail');*/


    Route::post('saveuser', 'Api\UserController@saveUser');
    Route::get('removeuser', 'Api\UserController@removeuser');
    Route::get('logout', 'Api\UserController@logout');
    Route::post('usernotifications', 'Api\UserController@usernotifications');
    
    Route::post('getfavouritecompanies', 'Api\FavouriteController@getfavouriteCompanies');
    Route::post('addtofavourite', 'Api\FavouriteController@addtofavourite');
    Route::post('removefavourite', 'Api\FavouriteController@removefavourite');

    Route::post('getcompanycustomers', 'Api\CustomerController@getcompanycustomers');
    Route::post('addtocustomers', 'Api\CustomerController@addtocustomers');
    Route::post('removecustomer', 'Api\CustomerController@removecustomer');

});

