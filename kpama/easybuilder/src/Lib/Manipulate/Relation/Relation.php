<?php

namespace Kpama\Easybuilder\Lib\Manipulate\Relation;

use Kpama\Easybuilder\Lib\Manipulate\Entity;

abstract class Relation
{

    protected $model;
    protected array $definition;
    protected array $fullDefinition;
    protected Entity $entity;
    protected array $data;
    protected string $relation;


    public function __construct(
        object $model,
        array $definition,
        array $data,
        string $relation,
        array $fullDefinition
    ) {
        $this->model  = $model;
        $this->definition = $definition;
        $this->entity = new Entity();
        $this->data = $data;
        $this->relation = $relation;
        $this->fullDefinition = $fullDefinition;
    }


    public abstract function apply(bool $remove = false): object;
}
