<?php

namespace Kpama\Easybuilder\Lib;

use Illuminate\Http\Request;
use Kpama\Easybuilder\Lib\Manipulate\Entity;

class Manipulator
{
    protected Entity $entity;

    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    public static function make(): Manipulator
    {
        return app()->make(Manipulator::class);
    }

    public function handleCreateOrUpdateRequest(Request $request, string $resource, string $id = null)
    {
        return $this->entity->createOrUpdate($this->resourceToClass($resource), $request->all(), ($id) ? 'edit' : 'create',$id);
    }

    public function handleCreateOrUpdateData(string $resource, array $data, string $id = null)
    {
        return $this->entity->createOrUpdate($this->resourceToClass($resource), $data, ($id) ? 'edit' : 'create', $id);
    }

    public function handleGetRequest(Request $request, string $resource, string $id = null)
    {
        return $this->handleGet($resource, $id, $request->query());
    }

    public function handleGet($resource, mixed $id = null, array $params = [])
    {
        return $this->entity->query($this->resourceToClass($resource), $id, $params);
    }

    public function getEntity(): Entity
    {
        return $this->entity;
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
}
