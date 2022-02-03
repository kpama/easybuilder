<?php

use Illuminate\Support\Facades\Route;
use Kpama\Easybuilder\Controllers\HelloController;

Route::group(['middleware' => ['web']], function () {

   // Add your web  routes here
   Route::get('kpamaeasybuilder','Kpama\Easybuilder\Controllers\HelloController@indexAction');
   Route::get('kpamaeasybuilder/swagger', [HelloController::class, 'swaggerAction']);
   Route::get('kpamaeasybuilder/swagger-api/{resource}', [HelloController::class, 'swaggerApiAction']);
   Route::get('kpamaeasybuilder/swagger-config', function () {
	   $links = [];
	   collect(Route::getRoutes())->each(function($route) use(&$links){
		   if(isset($route->defaults['_easy_generated']) && isset($route->defaults['resource']) ) {
			$resource = $route->defaults['resource'];
			$links[$resource] = [
				'url' => '/kpamaeasybuilder/swagger-api/' . $resource,
				'name' => str_replace('-', ' ', $resource)
		       ];
		   } 
	   
	   });


	   return [
		   'urls' =>  array_values($links)
	   ];
   });
});



if (config('env') != 'production') {
    /**
     * Make sure to publish your public assets when going live
     */
    Route::get('kpamaeasybuilder/{path?}', 'Kpama\Easybuilder\Controllers\AssetController@serveAction')->where('path', '(.*)');
}
