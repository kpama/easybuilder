<?php

namespace Kpama\Easybuilder\Lib\Manipulate\Relation;


class BelongsToMany extends Relation
{

    public function apply(bool $remove = false): object
    {
        if($remove) {

        } else {
            $relatedLocalKey = $this->definition['related_local_key'];
            $method = $this->definition['method'];
            $name = $this->definition['name'];



            $builtData = [];
            $pivotColumns = $this->getRelationTableColumns();

            foreach($this->data as $relationship) {
                if(isset($relationship[$relatedLocalKey])){
                    $cleanData = $this->entity->validate($relationship, $pivotColumns);
                    $id = $relationship[$relatedLocalKey];
                    $clean = [];
                    foreach($cleanData['clean'] as $field => $value) {
                        $clean[str_replace('pivot_','', $field)] = $value;
                    }
                    $builtData[$id] = $clean;
                }
            }

            if(!empty($builtData)) {
                $this->model->{$method}()->syncWithoutDetaching($builtData);
            }

        }

        return $this->model;
    }

    private function getRelationTableColumns(): array
    {
        $columns = [];

        foreach($this->definition['columns'] as $column) {
            if( strstr($column['name'], 'pivot_')  && !$column['is_primary'] && !$column['is_foreign_key'] && !$column['is_related_key']) {
                $columns[] = $column;
            }
        }

        return $columns;
    }
}
