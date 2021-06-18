<?php

namespace Kpama\Easybuilder\Lib;

use Kpama\Easybuilder\Lib\Manipulate\Entity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Query
{

    protected Request $request;
    protected array $definition;

    public function __construct(array $definition,  Request $request = null)
    {
        $this->request = $request ?? request();
    }
    public function build(string $class, Entity $entity): Builder
    {
        $query = $class::query();
        $query = $this->processRequestQuery($query);

        return $query;
    }

    protected function processRequestQuery(Builder $query): Builder
    {
        foreach ($this->request->query() as $name => $param) {
            switch ($name) {
                case 'with':
                    $query = $this->processWith($param, $query);
                    break;
            }
        }
        return $query;
    }

    protected function processWith(string $param, Builder $query): Builder
    {
        $relationships = explode(',', $param);

        foreach($relationships as $aRelationship) {

        }

        return $query;
    }
}
