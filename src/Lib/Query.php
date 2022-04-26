<?php

namespace Kpama\Easybuilder\Lib;

use Kpama\Easybuilder\Lib\Manipulate\Entity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Query
{

    protected Request $request;
    protected array $params = [];

    public function build(array $definition, array $params = []): Builder
    {
        $query = $definition['class']::query();
        $query = $this->processRequestQuery($query, $params);

        return $query;
    }

    protected function processRequestQuery(Builder $query, array $params = []): Builder
    {
        foreach ($params as $name => $param) {
            switch ($name) {
                case 'with':
                    $query = $this->processWith($param, $query);
                    break;
                case 'filter':
                    $query = $this->processFilters($param, $query);
                    break;
                case 'field':
                    $query = $this->processFields($param, $query);
                    break;
            }
        }
        return $query;
    }

    protected function processWith(string | array $param, Builder $query): Builder
    {
        $relationships =(is_array($param)) ? $param :  explode(',', $param);

        foreach ($relationships as $aRelationship) {

        }

        return $query;
    }

    protected function processFilters(array $filters, Builder $query): Builder
    {

        foreach ($filters as $field => $params) {
            foreach ($params  as $condition => $value) {
                switch ($condition) {
                    case 'eq': // equal
                    case 'is': // equal
                        $query->where($field, $value);
                        break;
                    case 'in': // in
                        $query->whereIn($field, (is_array($value)) ? $value: explode(',', $value));
                        break;
                    case 'sw': // start with
                        $query->where($field, 'like', "{$value}%");
                        break;
                    case 'ew': // end with
                        $query->where($field, 'like', "%{$value}");
                        break;
                    case 'lk': // like
                        $query->where($field, 'like', "%{$value}%");
                        break;
                    case 'g': // greater than
                        $query->where($field, '>', $value);
                        break;
                    case 'ge': // greater than or equal to
                        $query->where($field, '>=', $value);
                        break;
                    case 'l': // less than
                        $query->where($field, '<', $value);
                        break;
                    case 'le': // less than or equal to
                        $query->where($field, '<=', $value);
                        break;
                }
            }
        }
        return $query;
    }

    protected function processFields($param,  Builder $query): Builder
    {
        // @todo implement
        return $query;
    }
}
