<?php

use Illuminate\Support\Facades\Route;
use Kpama\Easybuilder\Controllers\HelloController;
use Kpama\Easybuilder\Controllers\SwaggerController;

Route::group(['middleware' => ['web']], function () {

   // Add your web  routes here
    // @todo Check configuration before registering these routes
    Route::get('kpamaeasybuilder', 'Kpama\Easybuilder\Controllers\HelloController@indexAction');
    Route::get('kpamaeasybuilder/swagger', [SwaggerController::class, 'swaggerAction']);
    Route::get('kpamaeasybuilder/swagger-api/{resource}', [SwaggerController::class, 'swaggerApiAction']);
    Route::get('kpamaeasybuilder/swagger-config', [SwaggerController::class, 'configAction']);


    if (config('env') != 'production') {
        /**
         * Make sure to publish your public assets when going live
         */
        Route::get('kpamaeasybuilder/{path?}', 'Kpama\Easybuilder\Controllers\AssetController@serveAction')->where('path', '(.*)');
    }
});