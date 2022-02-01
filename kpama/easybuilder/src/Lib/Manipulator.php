<?php

namespace Kpama\Easybuilder\Lib;

use Illuminate\Http\Request;
use Kpama\Easybuilder\Lib\Manipulate\Entity;

class Manipulator
{
    private Entity $entity;

    public function __construct()
    {
        $this->entity = new Entity();
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
        $class = $this->resourceToClass($resource);
        return $this->entity->query($class, $id);
    }

    public function handleGet()
    {
        // @todo Implement this method
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
