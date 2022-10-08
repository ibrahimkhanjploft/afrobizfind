<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('home');
});


Route::get('/privacypolicy', function () {
    return view('tearms');
});


Auth::routes(['register' => false, 'reset' => false]);

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('payment', 'Api\StripeController@payment')->name('payment');
Route::get('cancel', 'Api\StripeController@cancel')->name('payment.cancel');
Route::get('payment/success', 'Api\StripeController@success')->name('payment.success');

Route::get('payment/success_callback', 'Api\StripeController@success_call_back')->name('payment.success_call_back');
Route::get('payment/failed_callback', 'Api\StripeController@failed_callback')->name('payment.failed_callback');

Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {

    Route::get('/', 'admin\AdminController@index')->name('admin.home');

    Route::get('/versions', 'admin\VersionController@list')->name('admin.versions');
    Route::post('/version/getall', 'admin\VersionController@getallversions');
    Route::get('/version/add', 'admin\VersionController@add')->name('version.add');
    Route::get('/version/edit/{id}', 'admin\VersionController@edit');
    Route::get('/version/show/{id}', 'admin\VersionController@show');
    Route::post('/version/store', 'admin\VersionController@store')->name('version.store');
    Route::get('/version/delete/{id}', 'admin\VersionController@delete');

      /*
|--------------------------------------------------------------------------
| CURRENCY
|--------------------------------------------------------------------------
*/

Route::get('/currencies', 'admin\CurrencyController@list')->name('admin.currencies');
    Route::post('/currencies/getall', 'admin\CurrencyController@getallcurrencies');
    Route::get('/currencies/create', 'admin\CurrencyController@create')->name('currencies.create');
    Route::get('/currencies/edit/{id}', 'admin\CurrencyController@edit');
    Route::get('/currencies/show/{id}', 'admin\CurrencyController@show');
    Route::post('/currencies/store', 'admin\CurrencyController@store')->name('currencies.store');
    Route::get('/currencies/delete/{id}', 'admin\CurrencyController@delete');
});