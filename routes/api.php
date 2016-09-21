<?php
use Illuminate\Http\Request;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
*/

Route::group([
'prefix' => 'v1',
'namespace' => 'api_',
'middleware' => ['web'], //`web` causes CSRFToken checks
],
function(){
    Route::post('user', ['as'=>'user.create', 'uses'=>'userController@create']);
    Route::post('user/signin', ['as'=>'user.signin', 'uses'=>'userController@signin']);
    Route::get('user/{user_id}', ['as'=>'user.get', 'uses'=>'userController@get']);
});