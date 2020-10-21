<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::middleware('auth:api')->group(function () {
    Route::resource('products', 'ProductController');
    Route::patch('/products/{product}/image', 'ProductController@addImage');

    Route::post('/users/{user}/products', 'ProductUserController@store');
    Route::delete('/users/{user}/products/{productId}', 'ProductUserController@destroy');
    Route::get('/users/{user}/products', 'ProductUserController@show');
});



