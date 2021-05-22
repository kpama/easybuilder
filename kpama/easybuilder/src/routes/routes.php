<?php

use Illuminate\Support\Facades\Route;
use Kpama\Easybuilder\Controllers\HelloController;

Route::group(['middleware' => ['web']], function () {

   // Add your web  routes here
   Route::get('kpamaeasybuilder','Kpama\Easybuilder\Controllers\HelloController@indexAction');
   Route::get('test', [HelloController::class, 'testAction']);
   Route::get('apitest', [HelloController::class, 'apiTestAction']);

});



if (config('env') != 'production') {
    /**
     * Make sure to publish your public assets when going live
     */
    Route::get('kpamaeasybuilder/{path?}', 'Kpama\Easybuilder\Controllers\AssetController@serveAction')->where('path', '(.*)');
}
