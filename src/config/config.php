<?php

use Kpama\Easybuilder\Controllers\Api\V1\ApiController;

return [
    'version' => '0.0.1',

    /*----------------------------------------------------------------
     |  Allow the package to register routes for swagger documentation
     |----------------------------------------------------------------
    */
    'register_sawgger_route' => env('REGISTER_SWAGGER_ROUTE', true),

    /*----------------------------------------------------------------
     |  Prefix to use for the swagger routes
     |----------------------------------------------------------------
    */
    'swagger_uri_prefix' => env('SWAGGER_URI_PREFIX', 'kpamaeasybuilder'),

    /*----------------------------------------------------------------
     |  Default controller to use for API routes
     |----------------------------------------------------------------
    */
    'api_controller' => '\\'.ApiController::class
];