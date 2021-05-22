<?php

namespace Kpama\Easybuilder\Lib;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Kpama\Easybuilder\Lib\Manipulate\Entity;

class Manipulator
{
    private Entity $entity;

    public function __construct()
    {
        $this->entity = new Entity();
    }

    public function handleCreateOrUpdateRequest(Request $request, string $hash, string $id = null)
    {
        return $this->entity->createOrUpdate($this->decodeHash($hash), $request->all(), ($id) ? 'edit' : 'create', $id);
    }

    public function handleCreateOrUpdateData(string $hash, array $data, string $id = null)
    {
        return $this->entity->createOrUpdate($this->decodeHash($hash), $data, ($id) ? 'edit' : 'create', $id);
    }

    public function handleGetRequest()
    {
        // @todo Implement this method
    }

    public function handleGet()
    {
        // @todo Implement this method
    }

    protected function decodeHash(string $hash): string
    {
        return  base64_decode($hash);
    }
}
