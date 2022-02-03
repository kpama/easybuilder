<?php

namespace Kpama\Easybuilder\Lib\Api;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Kpama\Easybuilder\Controllers\Api\V1\ApiController;
use Kpama\Easybuilder\Lib\Parser;

class RouteBuilder
{
    public static function generate(string $model, $authApi = true): RouteCollection
    {
        $parser = new Parser();
        $data = $parser->parse($model);

        $slug = Str::slug($data['class']);

        $routes = Route::apiResource($slug, ApiController::class)
            ->parameter($slug, 'id')
            ->register();

        // check if we can restore
        if ($data['can_soft_delete']) {
            $routes->add(Route::match(['put', 'patch'], "{$slug}/restore/{id}", [ApiController::class, 'restore'])->name("{$slug}.restore"));
        }

        if (!empty($data['scopes'])) {
            $routes->add(Route::match(['get', 'head'], "{$slug}/scopes/{scope}", [ApiController::class, 'scope'])->name("{$slug}.scope"));
        }

        // @todo emit event to allow the user to add additional route

        collect($routes)->each(function ($route) use ($data, $authApi) {
            if($authApi) {
                $route->middleware('auth:api');
            }
            $route->defaults('resource', $data['resource']);
            $route->defaults('_easy_generated', true);
        });

        return $routes;
    }
}
