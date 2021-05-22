<?php

namespace Kpama\Easybuilder\Lib;

use Illuminate\Support\Facades\Validator;

class Manipulator
{

    public function createOrUpdate(string $resourceClass, array $data)
    {
        $parser = new Parser();
        $definition = $parser->parse($resourceClass);

        $result= $this->validate($data, $definition['columns']);

        $model = ($result['mode'] == 'create')? new $resourceClass: $resourceClass::findOrFail($result['id']);

        foreach($result['clean'] as $field => $value) {
            $model->{$field} = $value;
        }


        $model->save();

        return $model->toArray();
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
}
