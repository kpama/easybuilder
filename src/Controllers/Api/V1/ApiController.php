<?php

namespace Kpama\Easybuilder\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Kpama\Easybuilder\Contracts\ApiControllerInterface;
use Kpama\Easybuilder\Lib\Manipulator;

class ApiController extends BaseController implements ApiControllerInterface
{
  protected Manipulator $manipulator;

  public function __construct(Manipulator $manipulator)
  {
    $this->manipulator = $manipulator;
  }
  
  public function index(Request $request, string $resource)
  {
    return $this->manipulator->handleGetRequest($request, $resource);
  }

  public function scope(Request $request, string $scope, string $resource)
  {
    // @todo use the specified scope as the starting point
    return ['resource' => $resource, 'scope' => $scope];
  }

  public function store(Request $request, string $resource)
  {
    return $this->manipulator->handleCreateOrUpdateRequest($request, $resource);
  }

  public function show(Request $request, mixed $id, string $resource)
  {
    return $this->manipulator->handleGetRequest($request, $resource, $id);
  }

  public function update(Request $request, mixed $id, string $resource)
  {
    return $this->manipulator->handleCreateOrUpdateRequest($request, $resource, $id);
  }

  public function destroy(mixed $id, string $resource)
  {
    // @todo implement this
  }

  public function restore(mixed $id, string $resource)
  {
    // @todo implement this
  }
}