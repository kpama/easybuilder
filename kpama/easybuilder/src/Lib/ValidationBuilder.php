<?php

namespace Kpama\Easybuilder\Lib;

use Doctrine\DBAL\Types\Types;

class ValidationBuilder
{

    public static function buildRules(array $info): array
    {
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
                    $rules['create'][] = 'array';
                    $rules['edit'][] = 'array';
                    break;
                case Types::ASCII_STRING:
                case Types::STRING:
                case Types::TEXT:
                    $rules['create'][] = 'string';
                    $rules['edit'][] = 'string';
                    break;
                case Types::BIGINT:
                case 'int':
                case Types::INTEGER:
                case Types::SMALLINT:
                    $rules = self::intRules($rules, $info);
                    break;
                case Types::BINARY:
                case Types::BLOB:
                    $rules['create'][] = 'file';
                    $rules['edit'][] = 'file';
                    break;
                case Types::BOOLEAN:
                case 'bool':
                case 'boolean':
                    $rules['create'][] = 'boolean';
                    $rules['edit'][] = 'boolean';
                    break;
                case Types::DATE_MUTABLE:
                    $rules['create'][] = 'date';
                    $rules['edit'][] = 'date';
                    break;
                case Types::DATEINTERVAL:
                    // @todo
                    break;
                case Types::DATETIME_MUTABLE:
                    $rules['create'][] = 'date_format:Y-m-d H:i:s';
                    $rules['edit'][] = 'date_format:Y-m-d H:i:s';
                    break;
                case Types::DATETIMETZ_MUTABLE:
                    $rules['create'][] = 'date_format:Y-m-d H:i:s';
                    $rules['edit'][] = 'date_format:Y-m-d H:i:s';
                    break;
                case Types::DECIMAL:
                case Types::FLOAT:
                    $rules['create'][] = 'regex:^(?:[1-9]\d+|\d)(?:\,\d\d)?$';
                    $rules['edit'][] = 'regex:^(?:[1-9]\d+|\d)(?:\,\d\d)?$';
                    break;
                case Types::GUID:
                    $rules['create'][] = 'uuid';
                    $rules['edit'][] = 'uuid';
                    break;
                case Types::JSON:
                    $rules['create'][] = 'json';
                    $rules['edit'][] = 'json';
                    break;
                case Types::OBJECT:
                    // @todo
                    break;
                case Types::TIME_MUTABLE:
                    $rules['create'][] = 'date_format';
                    $rules['edit'][] = 'date_format';
                    break;
            }
            $info['validation_rules'] = $rules;
        }


        return $info;
    }

    private static function intRules(array $rules, array $info): array
    {
        $rules['create'][] = 'integer';
        $rules['edit'][] = 'integer';

        return $rules;
    }

    private static function appendRequiredRule(array $rules, array $info): array
    {
        if ($info['not_null']) {
            $rules['create'][] = 'required';

            if (
                !$info['is_relation'] &&
                !$info['is_foreing_key']
            ) {
                $rules['edit'][] = 'sometimes';
            }

            $rules['edit'][] = 'required';
        }

        return $rules;
    }
}
