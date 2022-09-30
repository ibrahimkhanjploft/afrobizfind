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

 
Auth::routes(['register' => false,'reset' => false]);
 
Route::group(['prefix'=>'admin','middleware' => 'admin'], function() { 

    Route::get('/', 'admin\AdminController@index')->name('admin.home');
   
    Route::get('/versions', 'admin\VersionController@list')->name('admin.versions');
    Route::post('/version/getall', 'admin\VersionController@getallversions');

    Route::get('/version/add', 'admin\VersionController@add')->name('version.add');
    Route::get('/version/edit/{id}', 'admin\VersionController@edit');
    Route::get('/version/show/{id}', 'admin\VersionController@show');
    Route::post('/version/store', 'admin\VersionController@store')->name('version.store');
    Route::get('/version/delete/{id}', 'admin\VersionController@delete');

});