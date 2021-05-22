<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Kpama\Easybuilder\Lib\Manipulator;

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

Route::resource('users', UserController::class);

Route::post('resource/{hash}', function(Request $request, $hash) {
    $manipulator = new Manipulator();
    return $manipulator->handleCreateOrUpdateRequest($request, $hash);
});

Route::put('resource/{hash}/{id}', function(Request $request, $hash, $id) {
    $manipulator = new Manipulator();
    return $manipulator->handleCreateOrUpdateRequest($request, $hash, $id);
});
