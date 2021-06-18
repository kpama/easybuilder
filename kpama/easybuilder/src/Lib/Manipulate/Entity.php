<?php

namespace Kpama\Easybuilder\Lib\Manipulate;

use Illuminate\Support\Facades\Validator;
use Kpama\Easybuilder\Lib\Manipulate\Relation\BelongsToMany;
use Kpama\Easybuilder\Lib\Manipulate\Relation\HasOne;
use Kpama\Easybuilder\Lib\Parser;
use Kpama\Easybuilder\Lib\Query;
use Spatie\QueryBuilder\QueryBuilder;
// use Illuminate\Database\Eloquent\Builder as QueryBuilder;

/**
 * Manipulate the base entity (The actual entity)
 */
class Entity
{
    const MODE_EDIT  = 'edit',
        MODE_CREATE  = 'create';

    public function createOrUpdate(string $resourceClass, array $data, ?string $mode = null, ?string $id = null)
    {
        $parser = new Parser();
        $definition = $parser->parse($resourceClass);;
        return $this->createOrUpdateWithDefinition($definition, $resourceClass, $data, $mode, $id);
    }

    public function createOrUpdateWithDefinition(array $definition, string $resourceClass, array $data, ?string $mode = null, ?string $id = null)
    {

        $result = $this->validate($data, $definition['columns'], $id);

        $idField = $this->getEntityIdField($definition);
        $mode = $mode ?? $result['mode'];

        if ($mode == self::MODE_EDIT) {
            $result[$idField] = ($id) ? $id : $result[$idField];
        }

        $model = ($mode == self::MODE_CREATE) ? new $resourceClass : $resourceClass::findOrFail($result[$idField]);


        foreach ($result['clean'] as $field => $value) {
            $model->{$field} = $value;
        }


        $model->save();

        $mode = $this->setAssociations($definition, $data, $model);

        return $model;
    }

    public function getEntityIdField(array $definition): string
    {

        $idField = 'id';

        foreach ($definition['columns'] as $column) {
            if ($column['is_primary']) {
                $idField = $column['name'];
                break;
            }
        }

        return $idField;
    }

    public function query(string $resourceClass, string $id = null)
    {
        $parser = new Parser();
        $definition = $parser->parse($resourceClass);
        return $this->queryWithDefinition($definition, $resourceClass, $id);
    }

    public function queryWithDefinition(array $definition, string $resourceClass, string $id = null)
    {
        $allowToFileBy = [];

        foreach ($definition['columns'] as $column) {
            if ($column['in_filter']) {
                $allowToFileBy[] = $column['name'];
            }
        }

        $query = (new Query($definition))->build($resourceClass, $this);

        if ($id) {
            return $query->findOrFail($id);
        } else {
            return $query->get();
        }
    }


    public function setAssociations(array $definition, array $data, object $model, bool $remove = false): object
    {
        if (isset($definition['_relationships'])) {
            foreach ($definition['_relationships'] as $name => $def) {
                if (isset($data[$name])) {
                    switch ($def['type']) {
                        case 'belongs_to_many':
                            $model = (new BelongsToMany($model, $def, $data[$name], $name, $definition))->apply($remove);
                            break;
                        case 'has_one':
                            $model = (new HasOne($model, $def, $data[$name], $name, $definition))->apply($remove);
                            break;
                    }
                }
            }
        }

        // do removes
        if (isset($data['_remove'])) {
            $model = $this->setAssociations($definition, $data['_remove'], $model, true);
        }

        return $model;
    }


    public function validate(array $data, array $columns, ?string $id = ''): array
    {
        $createRules = [];
        $editRules = [];
        $mode = $id ? self::MODE_EDIT : self::MODE_CREATE;
        $id = '';

        foreach ($columns as $definition) {
            $name = $definition['name'];
            $validationRules = $definition['validation_rules'];
            if ($definition['is_primary'] && isset($data[$name])) {
                $mode = self::MODE_EDIT;
                $id = $data[$name];
            }

            if (!empty($validationRules['create'])) {
                if ($this->shouldAddToValidator($name, $definition, $data, 'create')) {
                    $createRules[$name] = $validationRules['create'];
                }
            }
            if (!empty($validationRules['edit'])) {
                if ($this->shouldAddToValidator($name, $definition, $data, 'edit')) {
                    $editRules[$name] = $validationRules['edit'];
                }
            }
        }

        $rules = ($mode == self::MODE_CREATE) ? $createRules : $editRules;
        return [
            'clean' =>  Validator::make($data, $rules)->validate(),
            'mode' => $mode,
            'id' => $id
        ];
    }

    protected function shouldAddToValidator(string $name, array $definition,  array $data, string $mode): bool
    {
        if (!$definition['not_null'] && !isset($data[$name])) {
            return false;
        }

        return true;
    }

    protected function relationEntityAlreadyExist(): bool
    {
        return true;
    }
}
