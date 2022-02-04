<?php

namespace Kpama\Easybuilder\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
class HelloController extends BaseController
{

    public function indexAction()
    {
        return  view('kpamaeasybuilder::hello');
    }

    public function swaggerAction()
    {
        return view('kpamaeasybuilder::swagger');
    }

}
