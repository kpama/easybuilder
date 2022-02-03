<?php

use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Kpama\Easybuilder\Lib\Parser;
use Kpama\Easybuilder\Lib\Transformer\Form;
use Kpama\Easybuilder\Lib\Transformer\JsonSchema;

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

    return $parser->parse(Person::class);
});

Route::get('json-schema', function(){
    $parser = new Parser();
    return (new JsonSchema())->transform($parser->parse(Person::class));
});

Route::get('ui', function(){
    $parser = new Parser();

    return (new Form())->transform($parser->parse(User::class));
});
