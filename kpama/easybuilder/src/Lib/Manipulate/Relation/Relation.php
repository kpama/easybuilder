<?php

namespace Kpama\Easybuilder\Lib\Manipulate\Relation;

use Kpama\Easybuilder\Lib\Manipulate\Entity;

abstract class Relation {

    protected $model;
    protected array $definition;
    protected Entity $enity;
    protected array $data;


    public function __construct(object $model, array $definition, array $data)
    {
       $this->model  = $model; 
       $this->definition = $definition;
       $this->entity = new Entity();
       $this->data = $data;
    }


    public abstract function apply(bool $remove = false);
}