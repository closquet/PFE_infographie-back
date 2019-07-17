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

// Auth
Route::post('/register', 'Auth\AuthController@register')->name('user.register');
Route::post('/login', 'Auth\AuthController@login')->name('user.login');
Route::post('/refresh-token', 'Auth\AuthController@refresh')->name('user.refresh');
Route::post('/logout', 'Auth\AuthController@logout')->middleware('auth:api')->name('user.logout');

// Forgotten password
//TODO: replace log by email sending
Route::post('/password/create', 'Auth\PasswordResetController@create');
Route::get('/password/find/{token}', 'Auth\PasswordResetController@find');
Route::post('/password/reset', 'Auth\PasswordResetController@reset');


Route::group(['middleware' => ['auth:api', 'isadmin']], function () {
    Route::get('/users', 'UserController@index')->name('user.index');

});


Route::group(['middleware' => ['auth:api']], function () {

    Route::get('/user', 'UserController@showLoggedInUser')->name('user.showloggedInUser');
    Route::post('/user/avatar', 'UserController@updatAvatar')->name('user.update_avatar');
    Route::delete('/user/avatar', 'UserController@deleteAvatar')->name('user.delete_avatar');

    Route::get('/users/{slug}', 'UserController@showBySlug')->name('user.showBySlug');

});
