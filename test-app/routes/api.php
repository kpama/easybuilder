<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Kpama\Easybuilder\Lib\Manipulator;
use Kpama\Easybuilder\Lib\Api\RouteBuilder;
use App\Models\Person;
use App\Models\User;

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

Route::post('resource/{resource}', function(Request $request, $resource) {
    $manipulator = new Manipulator();
    return $manipulator->handleCreateOrUpdateRequest($request, $resource);
});

Route::put('resource/{resource}/{id}', function(Request $request, $resource, $id) {
    $manipulator = new Manipulator();
    return $manipulator->handleCreateOrUpdateRequest($request, $resource, $id);
});

Route::get('resource/{resource}', function(Request $request, $resource) {
    $manipulator = new Manipulator();
    return $manipulator->handleGetRequest($request, $resource);
});

Route::get('resource/{resource}/{id}', function(Request $request, $resource, $id) {
    $manipulator = new Manipulator();
    return $manipulator->handleGetRequest($request, $resource, $id);
});

RouteBuilder::generate(Person::class, false);
RouteBuilder::generate(User::class, false);
