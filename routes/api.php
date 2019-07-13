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

Route::post('/login', 'Auth\AuthController@login')->name('user.login');
Route::post('/register', 'Auth\AuthController@register')->name('user.register');
Route::post('/refresh', 'Auth\AuthController@refresh')->name('user.refresh');

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/logout', 'Auth\AuthController@logout')->name('user.logout');

});
