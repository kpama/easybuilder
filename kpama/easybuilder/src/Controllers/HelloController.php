<?php

namespace Kpama\Easybuilder\Controllers;

use Illuminate\Routing\Controller as BaseController;

class HelloController extends BaseController
{

    public function indexAction()
    {
       return  view('kpamaeasybuilder::hello');
    }
}