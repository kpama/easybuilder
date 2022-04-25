<?php

namespace Kpama\Easybuilder\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Route;
use Kpama\Easybuilder\Lib\Parser;
use Kpama\Easybuilder\Lib\Transformer\JsonSchema;
use Illuminate\Http\Request;

class SwaggerController extends BaseController
{
    public function swaggerAction()
    {
        return view('kpamaeasybuilder::swagger');
    }

    public function configAction()
    {
        $links = [];
        $prefix = config('kpamaeasybuilder.swagger_uri_prefix');
        collect(Route::getRoutes())->each(function ($route) use (&$links, $prefix) {
            if (isset($route->defaults['_easy_generated']) && isset($route->defaults['resource'])) {
                $resource = $route->defaults['resource'];
                $links[$resource] = [
                    'url' => "/{$prefix}/swagger-api/{$resource}",
                    'name' => str_replace('-', ' ', $resource)
                ];
            }
        });

        return [
            'urls' =>  array_values($links)
        ];
    }

    public function swaggerApiAction(Request $request, string $resource)
    {
        $builtRoutes = [];
        $definition = (new Parser())->parse($this->resourceToClass($resource));
        $schema = (new JsonSchema())->transform($definition);

        collect(Route::getRoutes())->each(function ($route) use ($resource, $schema,  &$routes, &$builtRoutes) {
            if (isset($route->defaults['resource']) && $route->defaults['resource'] == $resource) {
                $swaggerDefinition = [
                    'description' => implode(', ', $route->methods()) . " request to {$route->getActionName()} route handler",
                    'summary' => $route->getActionName(),
                    'parameters' => $this->buildParameterArray($route),
                    'responses' => $this->buildResponsesArray($route, $schema),
                ];

                $uri = $route->uri;
                $uri = ($uri[0] == '/') ? $uri : "/{$uri}";

                foreach ($route->methods() as $method) {
                    if ($method == 'HEAD') {
                        continue;
                    }

                    if (!isset($builtRoutes[$uri])) {
                        $builtRoutes[$uri] = [];
                    }
                    $swaggerDefinition['tags'] = [$method];
                    $builtRoutes[$uri][strtolower($method)] = $this->buildRequestBodyArray($route, $swaggerDefinition, $schema);
                }
            }
        });

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => env('APP_NAME'),
                'version' => '1.1',
                'description' => env('APP_NAME') . ' auto generated swagger docs'
            ],
            'servers' => [],
            'tags' => [
                [
                    'name' => 'GET',
                    'description' => "Get one or more {$className}"
                ],
                /*[
                    'name' => 'HEAD',
                    'description' => 'Make a get request'
                ],*/
                [
                    'name' => 'POST',
                    'description' => "Make a post request"
                ],
                [
                    'name' => 'PUT',
                    'description' => 'Make a put request'
                ],
                [
                    'name' => 'PATCH',
                    'description' => 'Make a put request'
                ],
                [
                    'name' => 'DELETE',
                    'description' => 'Make a delete request'
                ]
            ],
            'paths' => $builtRoutes
        ];
    }

    protected function resourceToClass(string $resource): string
    {
        $pieces = explode('-', $resource);
        foreach ($pieces as $index  => $name) {
            $pieces[$index] = ucfirst($name);
        }

        $class = implode('\\', $pieces);

        return $class;
    }


    protected function buildParameterArray(\Illuminate\Routing\Route $route): array
    {
        $specificParameters = [];


        if (!empty($route->parameterNames())) {
            preg_match_all('/\{(.*?)\}/', $route->getDomain() . $route->uri, $matches);

            foreach ($matches[1] as $name) {
                $isRequired  = (strpos($name, '?') === false) ? true : false;
                $cleanName = str_replace('?', '', $name);

                $specificParameters[] = [
                    'in' => 'path',
                    'name' => $cleanName,
                    'description' =>  $cleanName . ' ' . ($isRequired ? 'is a required field' :  ' is optional'),
                    'required' => $isRequired,
                    'schema' => [
                        'type' => 'string'
                    ]
                ];
            }
        }

        $specificParameters[] = [
            'in' => 'query',
            'name' => 'api_token',
            'description' =>  'Consumer API token',
            'required' => false,
            'schema' => [
                'type' => 'string'
            ]
        ];

        $parameters =  $specificParameters + [
            // trashed
            [
                'in' => 'query',
                'name' => 'trashed',
                'description' => 'Include trashed items',
                'schema' => [
                    'type' => 'integer',
                    'enum' => [0, 1]
                ]
            ],
            // sort
            [
                'in' => 'query',
                'name' => 'sort',
                'required' => false,
                'description' => 'Sort result by one or more fields [ascending => **col-name** , descending => **-col-name**]',
                'schema' => [
                    'type' => 'string',
                    'example' => '-id'
                ]
            ],
            // paginate page
            [
                'in' => 'query',
                'name' => 'page',
                'description' =>  'Number for the page to returned',
                'required' => false,
                'schema' => [
                    'type' => 'integer'
                ]
            ],
            // paginate limit
            [
                'in' => 'query',
                'name' => 'limit',
                'description' =>  'The number of resource to return per page',
                'required' => false,
                'schema' => [
                    'type' => 'integer'
                ]
            ],
        ];
        return $parameters;
    }

    protected function buildResponsesArray(\Illuminate\Routing\Route $route, $schema): array
    {
        $responses = [
            '201' => [
                'description' => 'Successful POST response',
                'content' => [
                    'application/json' => [
                        'schema'  => $schema
                    ]
                ]
            ],
            '200' => [
                'description' => 'Successful response',
                'content' => [
                    'application/json' => [
                        'schema'  => $schema
                    ]
                ]
            ],
            '404' => [
                'description' => 'Resource not found',
                'content' => [
                    'application/json' => [
                        'schema'  => [
                            'type' => 'object',
                            'properties' => [
                                'message' => [
                                    'type' => 'string'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $responses;
    }

    protected function buildRequestBodyArray(\Illuminate\Routing\Route $route, array $swaggerDefinition, $schema): array
    {
        if (!in_array('GET', $route->methods()) && !in_array('DELETE', $route->methods())) {
            $swaggerDefinition['requestBody'] = [
                'required' => true,
                'description' => 'This is an autogenerated doc. Please check the code base for payload structure',
                'content' => [
                    'application/json' => [
                        'schema' => $schema
                    ]
                ]
            ];
        }

        return $swaggerDefinition;
    }
}
