<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {

   // Add your web  routes here
   Route::get('kpamaeasybuilder','Kpama\Easybuilder\Controllers\HelloController@indexAction');

});



if (config('env') != 'production') {
    /**
     * Make sure to publish your public assets when going live
     */
    Route::get('kpamaeasybuilder/{path?}', 'Kpama\Easybuilder\Controllers\AssetController@serveAction')->where('path', '(.*)');
}
