<?php
declare(strict_types = 1);

namespace Kpama\Easybuilder\Lib\Manipulate\Relation;

class MorphToMany extends Relation {


    public function apply(bool $remove = false): object
    {
       return $this->model;
    }
}
