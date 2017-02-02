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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::group(['namespace' => 'Api'], function () {

    // Reader's Heartbeat
    Route::post('reader/heartbeat', 'ReaderController@heartbeat');

    // Planogram Setup
    Route::post('reader/setup', 'ReaderController@setup');

    // Inventory Data from the Reader
    Route::post('inventory/upload', 'InventoryController@upload');
});