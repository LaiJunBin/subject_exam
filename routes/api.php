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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::namespace('api')->group(function(){
    Route::prefix('categories')->group(function(){
        Route::get('/', 'ApiController@index');
        Route::get('/{id}', 'ApiController@show');
        Route::get('/{id}/questions', 'ApiController@getQuestions');
    });

    Route::post('/exam', 'ApiController@getExam');
});

// Route::group([
//     'namespace' => 'api',
//     'prefix' => 'api'
// ], function(){
//     Route::apiResource('categories', 'CategoryController');
// });