<?php

namespace Kpama\Easybuilder\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Kpama\Easybuilder\Lib\Manipulator;

class ApiController extends BaseController
{
  public function index(Request $request, string $resource)
  {
    return (new Manipulator())->handleGetRequest($request, $resource);
  }

  public function scope(Request $request, string $scope, string $resource)
  {
    return ['resource' => $resource, 'scope' => $scope];
  }

  public function store(Request $request, string $resource)
  {
    return (new Manipulator())->handleCreateOrUpdateRequest($request, $resource);
  }

  public function show(Request $request, mixed $id, string $resource)
  {
    return (new Manipulator())->handleGetRequest($request, $resource, $id);
  }

  public function update(Request $request, mixed $id, string $resource)
  {
    return (new Manipulator())->handleCreateOrUpdateRequest($request, $resource, $id);
  }

  public function destroy(mixed $id, string $resource)
  {

  }

  public function restore(mixed $id, string $resource)
  {

  }
}