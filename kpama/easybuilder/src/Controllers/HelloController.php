<?php

namespace Kpama\Easybuilder\Controllers;

use App\Models\Person;
use Illuminate\Routing\Controller as BaseController;
use Kpama\Easybuilder\Lib\Parser;

class HelloController extends BaseController
{

    public function indexAction()
    {
       return  view('kpamaeasybuilder::hello');
    }

    public function testAction()
    {
        $parser = new Parser();
        return $parser->parse(Person::class);
    }

    public function apiTestAction()
    {
        
    }
}