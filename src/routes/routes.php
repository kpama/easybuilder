<?php

use Illuminate\Support\Facades\Route;
use Kpama\Easybuilder\Controllers\HelloController;
use Kpama\Easybuilder\Controllers\SwaggerController;

Route::group(['middleware' => ['web']], function () {

	// Add your web  routes here
	Route::get('kpamaeasybuilder', 'Kpama\Easybuilder\Controllers\HelloController@indexAction');

	// swagger routes
	if (config('kpamaeasybuilder.register_sawgger_route')) {
		$prefix = config('kpamaeasybuilder.swagger_uri_prefix');
		Route::get("{$prefix}/swagger", [SwaggerController::class, 'swaggerAction']);
		Route::get("{$prefix}/swagger-api/{resource}", [SwaggerController::class, 'swaggerApiAction']);
		Route::get("{$prefix}/swagger-config", [SwaggerController::class, 'configAction']);
	}

	if (config('env') != 'production') {
		/**
		 * Make sure to publish your public assets when going live
		 */
		Route::get('kpamaeasybuilder/{path?}', 'Kpama\Easybuilder\Controllers\AssetController@serveAction')->where('path', '(.*)');
	}
});
