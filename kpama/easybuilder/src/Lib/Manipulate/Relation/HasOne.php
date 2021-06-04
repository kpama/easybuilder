<?php

namespace Kpama\Easybuilder\Lib\Manipulate\Relation;

class HasOne extends Relation
{

    public function apply(bool $remove = false): object
    {

        $idField = $this->entity->getEntityIdField($this->definition);
        $isNew = false;

        if (!isset($this->data[$idField])) {
            $isNew = true;
            $value = $this->model->{$this->definition['local_key']};
            $this->data[$this->definition['foreign_key']] = $value;

            $model = $this->entity->createOrUpdateWithDefinition(
                $this->definition,
                $this->definition['class'],
                $this->data
            );
        }

        if(!$isNew) {
            $model = $this->entity->createOrUpdateWithDefinition(
                $this->definition,
                $this->definition['class'],
                $this->data
            );
        }

        if($remove) {
            $this->model->{$this->relation}()->delete();
        } else {
            $this->model->{$this->relation}()->save($model);
        }

        return $this->model;
    }
}
