<?php

namespace Kpama\Easybuilder\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ApiController extends BaseController
{
  public function index(Request $request, string $resource)
  {
    return ['resource' => $resource];
  }

  public function scope(Request $request, string $scope, string $resource)
  {
    return ['resource' => $resource, 'scope' => $scope];
  }

  public function store(Request $request, string $resource)
  {

  }

  public function show(mixed $id, string $resource)
  {

  }

  public function update(Request $request, mixed $id, string $resource)
  {

  }

  public function destroy(mixed $id, string $resource)
  {

  }

  public function restore(mixed $id, string $resource)
  {

  }
}