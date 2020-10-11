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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('contacts', 'ContactsController@load');
Route::get('contacts/{id}/preview', 'ContactsController@preview');

Route::get('templates', 'TemplatesController@index');
Route::post('templates', 'TemplatesController@create');
Route::post('templates/{id}', 'TemplatesController@update');
Route::delete('templates/{id}', 'TemplatesController@delete');
