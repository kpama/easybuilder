<?php

use App\Models\Group;
use App\Models\Person;
use App\Models\Role;
use App\Models\Secret;
use App\Models\Software;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Kpama\Easybuilder\Lib\Parser;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function(){
    $parser = new Parser();

    return $parser->parse(Software::class);
});
