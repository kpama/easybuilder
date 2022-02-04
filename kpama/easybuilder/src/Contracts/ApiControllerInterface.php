<?php
declare(strict_types=1);

namespace Kpama\Easybuilder\Contracts;

use Illuminate\Http\Request;

interface ApiControllerInterface
{
  public function index(Request $request, string $resource);

  public function scope(Request $request, string $scope, string $resource);

  public function store(Request $request, string $resource);

  public function show(Request $request, mixed $id, string $resource);

  public function update(Request $request, mixed $id, string $resource);

  public function destroy(mixed $id, string $resource);

  public function restore(mixed $id, string $resource);
}