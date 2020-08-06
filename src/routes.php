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

Route::namespace('alimianesa\TopToll\Http\Controllers')->prefix('api')->group(function () {
    Route::prefix('toll')->group(function () {
        Route::get('/add/plate', "TollController@addPlate");
        Route::post('/get/bill', "TollController@getBill");
        Route::post('/add/plate', "TollController@addPlate");
    });
});

