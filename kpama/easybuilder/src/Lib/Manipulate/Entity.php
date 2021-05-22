<?php

namespace Kpama\Easybuilder\Lib\Manipulate;

use Illuminate\Support\Facades\Validator;
use Kpama\Easybuilder\Lib\Manipulate\Relation\HasOne;
use Kpama\Easybuilder\Lib\Parser;

/**
 * Manipulate the base entity (The actual entity)
 */
class Entity
{
    public function createOrUpdate(string $resourceClass, array $data, ?string $mode = null, ?string $id = null)
    {
        $parser = new Parser();
        $definition = $parser->parse($resourceClass);

        $result = $this->validate($data, $definition['columns']);

        $mode = $mode ?? $result['mode'];
        $result['id'] = ($id) ? $id : $result['id'];

        $model = ($mode == 'create') ? new $resourceClass : $resourceClass::findOrFail($result['id']);


        foreach ($result['clean'] as $field => $value) {
            $model->{$field} = $value;
        }


        $model->save();

        $mode = $this->setAssociations($definition, $data, $model);

        return $model;
    }

    public function createOrUpdateWithDefinition(array $definition, string $resourceClass, array $data, ?string $mode = null, ?string $id = null)
    {
        $result = $this->validate($data, $definition['columns']);

        $mode = $mode ?? $result['mode'];
        $result['id'] = ($id) ? $id : $result['id'];

        $model = ($mode == 'create') ? new $resourceClass : $resourceClass::findOrFail($result['id']);


        foreach ($result['clean'] as $field => $value) {
            $model->{$field} = $value;
        }


        $model->save();

        $mode = $this->setAssociations($definition, $data, $model);

        return $model;
    }


    public function setAssociations(array $definition, array $data, object $model, bool $remove = false): object
    {
        if (isset($definition['_relationships'])) {
            foreach ($definition['_relationships'] as $name => $def) {
                if (isset($data[$name])) {
                    switch ($def['definition']['type']) {
                        case 'has_one':
                            (new HasOne($model, $def, $data[$name]))->apply($remove);
                            break;
                    }
                }
            }
        }

        return $model;
    }


    protected function validate(array $data, array $columns): array
    {
        $createRules = [];
        $editRules = [];
        $mode = 'create';
        $id = '';

        foreach ($columns as $name => $definition) {
            $validationRules = $definition['validation_rules'];
            if ($definition['is_primary'] && isset($data[$name])) {
                $mode = 'edit';
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


        $rules = ($mode == 'create') ? $createRules : $editRules;
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
