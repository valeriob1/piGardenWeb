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

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

// piGarden's log_send() posts here via curl -d (POST); a GET route that
// creates records would violate HTTP semantics and invite accidental writes
Route::middleware('auth:api')->post('/log', "ApiController@postLog");



