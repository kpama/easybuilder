<?php

namespace Kpama\Easybuilder\Lib;

use Doctrine\DBAL\Types\Types;
use Illuminate\Support\Facades\Validator;

class ValidationBuilder
{

    public static function buildRules(array $info): array
    {
        // NOTE: The keys for the rules are not to be use for validation
        //       The value for those keys are the real rules

        $rules = [
            'create' => [],
            'edit' => []
        ];

        if (
            $info['is_auto_increment'] ||
            $info['is_accessor'] ||
            (!$info['in_create'] && !$info['in_update'])
        ) {
            $info['validation_rules'] = $rules;
        } else {

            $rules = self::appendRequiredRule($rules, $info);

            switch ($info['type_name']) {
                case Types::ARRAY:
                case Types::SIMPLE_ARRAY:
                    $rules['create']['type'] = 'array';
                    $rules['edit']['type'] = 'array';
                    break;
                case Types::ASCII_STRING:
                case Types::STRING:
                case Types::TEXT:
                    $rules['create']['type'] = 'string';
                    $rules['edit']['type'] = 'string';
                    if($info['not_null']) {
                        $rules['create']['min'] = 'min:1';
                        $rules['edit']['min'] = 'min:1';
                    }

                    if($info['length']) {
                        $rules['create']['max'] = 'max:'. $info['length'];
                        $rules['edit']['max'] = 'max:'.$info['length'];
                    }
                    break;
                case Types::BIGINT:
                case 'int':
                case Types::INTEGER:
                case Types::SMALLINT:
                    $rules = self::intRules($rules, $info);
                    break;
                case Types::BINARY:
                case Types::BLOB:
                    $rules['create']['type'] = 'file';
                    $rules['edit']['type'] = 'file';
                    break;
                case Types::BOOLEAN:
                case 'bool':
                case 'boolean':
                    $rules['create']['type'] = 'boolean';
                    $rules['edit']['type'] = 'boolean';
                    break;
                case Types::DATE_MUTABLE:
                    $rules['create']['type'] = 'string';
                    $rules['edit']['type'] = 'string';
                    $rules['create']['date'] = 'date:Y-m-d';
                    $rules['edit']['date'] = 'date:Y-m-d';
                    break;
                case Types::DATEINTERVAL:
                    // @todo
                    break;
                case Types::DATETIME_MUTABLE:
                    $rules['create']['type'] = 'string';
                    $rules['edit']['type'] = 'string';
                    $rules['create']['date_format'] = 'date_format:Y-m-d H:i:s';
                    $rules['edit']['date_format'] = 'date_format:Y-m-d H:i:s';
                    break;
                case Types::DATETIMETZ_MUTABLE:
                    $rules['create']['type'] = 'string';
                    $rules['edit']['type'] = 'string';
                    $rules['create']['date_format'] = 'date_format:Y-m-d H:i:s';
                    $rules['edit']['date_format'] = 'date_format:Y-m-d H:i:s';
                    break;
                case Types::DECIMAL:
                case Types::FLOAT:
                    $rules['create']['type'] = 'numeric';
                    $rules['edit']['type'] = 'numeric';
                    $rules['create']['regex'] = 'regex:^(?:[1-9]\d+|\d)(?:\,\d\d)?$';
                    $rules['edit']['regex'] = 'regex:^(?:[1-9]\d+|\d)(?:\,\d\d)?$';
                    break;
                case Types::GUID:
                    $rules['create']['type'] = 'uuid';
                    $rules['edit']['type'] = 'uuid';
                    break;
                case Types::JSON:
                    $rules['create']['type'] = 'json';
                    $rules['edit']['type'] = 'json';
                    break;
                case Types::OBJECT:
                    // @todo
                    break;
                case Types::TIME_MUTABLE:
                    $rules['create']['date_format'] = 'date_format:H:i:s';
                    $rules['edit']['date_format'] = 'date_format:H:i:s';
                    break;
            }
            $info['validation_rules'] = $rules;
        }


        return $info;
    }

    public static function validate(array $definition, array $data, bool $creating = true): array
    {
        $rules = [];
        
        return Validator::make($data, $rules)->validate();
    }

    private static function intRules(array $rules, array $info): array
    {
        $rules['create']['type'] = 'integer';
        $rules['edit']['type'] = 'integer';

        return $rules;
    }

    private static function appendRequiredRule(array $rules, array $info): array
    {
        if ($info['not_null']) {
            $rules['create']['required'] = 'required';

            if (!$info['is_relation'] && !$info['is_foreign_key']) {
                $rules['edit']['sometiems'] = 'sometimes';
            }

            $rules['edit']['required'] = 'required';
        }

        return $rules;
    }
}
