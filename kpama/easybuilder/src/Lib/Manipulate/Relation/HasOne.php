<?php

namespace Kpama\Easybuilder\Lib\Manipulate\Relation;

class HasOne extends Relation
{

    public function apply(bool $remove = false)
    {
        dd($this->definition);

        $model = $this->entity->createOrUpdateWithDefinition(
            $this->definition['definition'],
            $this->definition['definition']['class'],
            $this->data
        );
        dump($this->definition);
        dd('we are in "has one" relation class');
    }
}
