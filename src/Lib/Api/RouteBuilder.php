<?php

namespace Kpama\Easybuilder\Lib\Api;

use Closure;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Kpama\Easybuilder\Lib\Parser;

class RouteBuilder
{
    public static function generate(string|array $model, ?Closure $forEachRoute = null, ?string $controller = null, string $slug = null)
    {
        if(is_array($model)) {
            foreach($model as $aModel) {
                self::generate($aModel, $forEachRoute, $controller);
            }
            return;
        }

        $parser = new Parser();
        $data = $parser->parse($model);

        $slug = $slug ?: Str::plural(Str::slug($data['class']));
        $controller = $controller ?: config('kpamaeasybuilder.api_controller');

        $routes = Route::apiResource($slug, $controller)
            ->parameter($slug, 'id')
            ->register();


        // check if we can restore
        if ($data['can_soft_delete']) {
            $routes->add(Route::match(['put', 'patch'], "{$slug}/restore/{id}", [$controller, 'restore'])->name("{$slug}.restore"));
        }

        if (!empty($data['scopes'])) {
            $routes->add(Route::match(['get', 'head'], "{$slug}/scopes/{scope}", [$controller, 'scope'])->name("{$slug}.scope"));
        }

        // @todo emit event to allow the user to add additional route

        collect($routes)->each(function ($route) use ($data, $forEachRoute) {
            $route->defaults('resource', $data['resource']);
            $route->defaults('_easy_generated', true);

            if($forEachRoute) {
                $forEachRoute($route);
            }
        });
    }
}
