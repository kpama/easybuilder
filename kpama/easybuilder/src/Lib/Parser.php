<?php

namespace Kpama\Easybuilder\Lib;

use Closure;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use ReflectionMethod;

class Parser
{

    protected string $relationRegex;

    protected static $relations = [
        'hasOne',
        'belongsTo',
        'hasMany',
        'hasOneThrough',
        'hasManyThrough',
        'belongsToMany',
        'morphTo',
        'morphOne',
        'morphMany',
        'morphToMany',
        'morphedByMany',
    ];


    public function __construct()
    {
        $this->relationRegex = '/^return\s+\$this->(' . implode('|', self::$relations) . ')/m';
    }

    public function parse($modelClass, bool $appendRelationships = true)
    {
        $model =  (is_object($modelClass)) ? $modelClass : $modelClass::make();

        $columns = $this->getTableColumns($model->getTable(), []);

        $result = $this->reflectClass($model, $appendRelationships, $columns);

        $class = get_class($model);

        $result['class'] = $class;
        $result['resource'] = $this->classToSlug($class);

        ksort($result);

        return $result;

    }

    protected function parseRelation($relation, &$columns, array $definition)
    {
        $definition['in_filter'] = true;
        $definition['in_read'] = true;
        $definition['resource'] = $this->classToSlug(get_class($relation->getRelated())); 
        $result = [];

        switch (get_class($relation)) {
            case BelongsToMany::class:
                $result = $this->parseBelongsToMany($relation, $columns);
                break;
            case HasMany::class:
                $result = $this->parseHasMany($relation, $columns);
                break;
            case HasOne::class:
                $result = $this->parseHasOne($relation, $columns);
                break;
            case HasManyThrough::class:
                $result = $this->parseHashManyThrough($relation, $columns);
                break;
            case HasOneThrough::class:
                break;
            case MorphOne::class:
                $result = $this->parseMorphOne($relation, $columns);
                break;
            case MorphMany::class:
                $result =$this->parseMorphMany($relation, $columns);
                break;
            case MorphTo::class:
                $result = $this->parseMorphTo($relation, $columns);
                break;
            case BelongsTo::class:
                $result = $this->parseBelongsTo($relation, $columns);
                break;
            case MorphToMany::class:
                $result = $this->pareseMorphToMany($relation, $columns);
                break;
            default:
                throw new \Exception('relationship not handled: '. get_class($relation));
                /* if ($relation instanceof BelongsToMany) {
                    $result = $this->parseBelongsToMany($relation, $columns);
                } elseif ($relation instanceof HasMany) {
                    $result = $this->parseHasMany($relation, $columns);
                } elseif ($relation instanceof HasOne) {
                    $result = $this->parseHasOne($relation, $columns);
                } elseif ($relation instanceof HasManyThrough) {
                    $result = $this->parseHashManyThrough($relation, $columns);
                } elseif ($relation instanceof MorphOne) {
                    $result = $this->parseMorphOne($relation, $columns);
                } elseif ($relation instanceof MorphMany) {
                    $result = $this->parseMorphMany($relation, $columns);
                } elseif ($relation instanceof MorphTo) {
                    $result = $this->parseMorphTo($relation, $columns);
                } elseif ($relation instanceof BelongsTo) {
                    $result = $this->parseBelongsTo($relation, $columns);
                } elseif ($relation instanceof MorphToMany) {
                    $result = $this->pareseMorphToMany($relation, $columns);
                }
                break; */
        }

        return array_merge($definition,$result);
    }

    protected function pareseMorphToMany($relation)
    {
        $result = [
            'type' => 'morph_to_many',
            'class' => get_class($relation->getRelated()),
            'foreign_key' => $relation->getForeignPivotKeyName(),
            'parent_foreign_key' => $relation->getRelatedPivotKeyName(),
            'local_key' => $relation->getRelatedKeyName(),
            'parent_local_key' => $relation->getParentKeyName(),
            'pivot_class' => $relation->getPivotClass(),
            'morph_class' => $relation->getMorphClass(),
        ];

        $typeName = $relation->getMorphType();

        $columns = $this->getTableColumns($relation->getTable(), [], function ($data, $column) use ($relation, $typeName) {
            $data['name'] = 'pivot_' . $data['name'];
            $data['is_foreign_key'] = $relation->getForeignPivotKeyName() == $column->getName();
            $data['is_related_key'] = $relation->getRelatedPivotKeyName() == $column->getName();
            $data['is_relation'] = $data['is_related_key'];

            if ($data['is_related_key']) {
                $data['class'] = get_class($relation->getRelated());
            }

            if ($column->getName() == $typeName) {
                $data['in_read'] = false;
                $data['in_create'] = false;
                $data['in_update'] = false;
                $data['in_quick_view'] = false;
            }


            return ValidationBuilder::buildRules($data);
        });

        $re = $this->reflectClass($result['pivot_class'], false, $columns);
        $result['columns'] = $re['columns'];

        return $result;
    }

    protected function parseMorphMany($relation)
    {

        $result =  [
            'type' => 'morph_many',
            'class' => get_class($relation->getRelated()),
            'foreign_key' => $relation->getForeignKeyName(),
            'local_key' => $relation->getLocalKeyName(),
        ];
        $columns = $this->getTableColumns($relation->getRelated()->getTable(), [], function ($data, $column) use ($relation) {
            return ValidationBuilder::buildRules($data);
        });

        $result['columns'] = $columns;

        return $result;
    }

    protected function parseHasOne(Relation $relation)
    {
        $result = [
            'type' => 'has_one',
            'class' => get_class($relation->getRelated()),
            'foreign_key' => $relation->getForeignKeyName(),
            'local_key' => $relation->getLocalKeyName(),
        ];

        $columns = $this->getTableColumns($relation->getRelated()->getTable(), [], function ($data, $column) use ($relation) {
            return ValidationBuilder::buildRules($data);
        });

        $result['columns'] = $columns;

        return $result;
    }

    protected function parseBelongsTo(Relation $relation, &$column)
    {
        $foreingKeyName =  $relation->getForeignKeyName();
        $column[$foreingKeyName]['is_foreign_key'] = true;
        $column[$foreingKeyName]['is_relation'] = true;
        $column[$foreingKeyName]['relation_name'] = $relation->getRelationName();

        $result = [
            'type' =>  'belongs_to',
            'class' => get_class($relation->getRelated()),
            'foreign_key' => $relation->getOwnerKeyName(),
            'local_key' => $foreingKeyName,
        ];

        $columns = $this->getTableColumns($relation->getRelated()->getTable(), [], function ($data, $column) use ($relation) {
            return ValidationBuilder::buildRules($data);
        });

        $result['columns'] = $columns;

        return $result;
    }

    protected function parseMorphTo($relation, &$columns)
    {

        $typeName = $relation->getMorphType();
        $foreingKeyName = $relation->getForeignKeyName();

        if ($columns[$typeName]) {
            $columns[$typeName]['in_read'] = false;
            $columns[$typeName]['in_create'] = false;
            $columns[$typeName]['in_update'] = false;
            $columns[$typeName]['in_quick_view'] = false;
            $columns[$typeName] = ValidationBuilder::buildRules($columns[$typeName]);
        }

        if ($columns[$foreingKeyName]) {
            $columns[$foreingKeyName]['in_read'] = false;
            $columns[$foreingKeyName]['in_create'] = false;
            $columns[$foreingKeyName]['in_update'] = false;
            $columns[$foreingKeyName]['in_quick_view'] = false;
            $columns[$foreingKeyName] = ValidationBuilder::buildRules($columns[$foreingKeyName]);
        }

        return [
            'type' =>  'morph_to'
        ];
    }

    protected function parseMorphOne($relation)
    {
        $result = [
            'type' => 'morph_one',
            'class' => get_class($relation->getRelated()),
            'foreign_key' => $relation->getForeignKeyName(),
            'local_key' => $relation->getLocalKeyName(),
            'columns' => []
        ];

        $columns = $this->getTableColumns($relation->getRelated()->getTable(), [], function ($data, $column) use ($relation) {
            return ValidationBuilder::buildRules($data);
        });

        $result['columns'] = $columns;

        return $result;
    }

    protected function parseHashManyThrough($relation)
    {
        $result = [
            'type' => 'has_many_through',
            'through_class' => get_class($relation->getParent()),
            'class' => get_class($relation->getRelated()),
            'foreign_key' => $relation->getFirstKeyName(),
            'local_key' => $relation->getLocalKeyName(),
            'columns' => []
        ];

        $columns = $this->getTableColumns($relation->getRelated()->getTable(), [], function ($data, $column) use ($relation) {
            $data['is_foreign_key'] = $relation->getForeignKeyName() == $column->getName();
            $data['is_related_key'] = $relation->getForeignKeyName() == $column->getName();
            if ($data['is_related_key']) {
                $data['is_relation'] = true;
            }
            return ValidationBuilder::buildRules($data);
        });

        $result['columns'] = $columns;

        return $result;
    }

    protected function parseOneThrough($relation)
    {
        $result = [
            'type' => 'has_one_through',
            'through_class' => get_class($relation->getParent()),
            'class' => get_class($relation->getRelated()),
            'foreign_key' => $relation->getFirstKeyName(),
            'local_key' => $relation->getLocalKeyName(),
            'columns' => []
        ];

        $columns = $this->getTableColumns($relation->getRelated()->getTable(), [], function ($data, $column) use ($relation) {
            $data['is_foreign_key'] = $relation->getForeignKeyName() == $column->getName();
            $data['is_related_key'] = $relation->getForeignKeyName() == $column->getName();
            if ($data['is_related_key']) {
                $data['is_relation'] = true;
            }
            return ValidationBuilder::buildRules($data);
        });

        $result['columns'] = $columns;

        return $result;
    }

    protected function parseHasMany($relation): array
    {
        $result = [
            'type' => 'has_many',
            'class' => get_class($relation->getRelated()),
            'foreign_key' => $relation->getForeignKeyName(),
            'local_key' => $relation->getLocalKeyName(),
            'columns' => []
        ];

        $columns = $this->getTableColumns($relation->getRelated()->getTable(), [], function ($data, $column) use ($relation) {
            $data['is_foreign_key'] = $relation->getForeignKeyName() == $column->getName();
            $data['is_related_key'] = $relation->getForeignKeyName() == $column->getName();
            if ($data['is_related_key']) {
                $data['is_relation'] = true;
            }
            return ValidationBuilder::buildRules($data);
        });

        

        $result['columns'] = $columns;

        return $result;
    }
    protected function parseBelongsToMany(Relation $relation): array
    {
        $result = [
            'type' =>  'belongs_to_many',
            'columns' => [],
            'class' => get_class($relation->getRelated()),
            'foreign_key' => $relation->getForeignPivotKeyName(),
            'local_key' => $relation->getParentKeyName(),
            'related_local_key' => $relation->getRelatedKeyName()
        ];


        $columns = $this->getTableColumns($relation->getTable(), [], function ($data, $column) use ($relation) {
            $data['name'] = 'pivot_' . $data['name'];
            $data['is_foreign_key'] = $relation->getForeignPivotKeyName() == $column->getName();
            $data['is_related_key'] = $relation->getRelatedPivotKeyName() == $column->getName();
            if ($data['is_related_key']) {
                $data['class'] = get_class($relation->getRelated());
                $data['is_relation'] = true;
            }

            return ValidationBuilder::buildRules($data);
        });

        $ref = $this->reflectClass($result['class'], false, $columns);
        $result['columns'] = $ref['columns'];

        return $result;
    }

    protected function getTableColumns(string $table, array $columns = [],  Closure $callback = null): array
    {
        $columnObjs = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableColumns($table);

        $tableObj = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableDetails($table);

        $primaryIds = [];
        if ($tableObj->hasPrimaryKey()) {
            $primaryIds = collect($tableObj->getPrimaryKeyColumns())->map(function ($column) {
                return (is_object($column)) ? $column->getName() : $column;
            })->toArray();
        }

        foreach ($columnObjs as $aColumn) {
            $data = [
                'name' => $aColumn->getName(),
                'label' => $aColumn->getName(),
                'not_null' => $aColumn->getNotnull(),
                'default' => $aColumn->getDefault(),
                'type_name' => $aColumn->getType()->getName(),
                'length' => $aColumn->getLength(),
                'is_auto_increment' => $aColumn->getAutoincrement(),
                'is_relation' => false,
                'is_primary' => in_array($aColumn->getName(), $primaryIds),
                'in_filter' => true,
                // 'options' => $aColumn->getCustomSchemaOptions()
            ];

            if(!$data['not_null'] && $data['type_name'] == 'string') {
                $data['min_length'] = 1;
            }

            // flags
            $data['in_read'] = true;
            $data['in_create'] = !$data['is_primary'];
            $data['in_update'] = !$data['is_primary'];
            $data['in_quick_view'] = true;

            $data = $this->getColumnMeta($data);
            $data = ValidationBuilder::buildRules($data);

            $columns[$data['name']] = ($callback) ? $callback($data, $aColumn) : $data;
        }

        return $columns;
    }

    private function getColumnMeta(array $data = [])
    {
        $default =  [
            'name' => '',
            'not_null' => '',
            'default' => '',
            'type_name' => '',
            'length' => '',
            'is_auto_increment' => false,
            'is_relation' => false,
            'is_primary' => false,
            'in_read' => false,
            'in_create' => false,
            'in_update' => false,
            'in_quick_view' => false,
            'is_accessor' => false,
            'is_mutator' => false,
            'validation_rules' => [],
            'is_foreign_key' => false,
        ];

        foreach ($data as $key => $value) {
            $default[$key] = $value;
        }

        return $default;
    }

    private function classToSlug(string $className): string
    {
        return str_replace('\\', '-', $className);
    }

    private function reflectClass($class, $appendRelationships = true, $columns)
    {
        $temp = new class
        {
            use HasRelationships;
        };

        $model = (is_object($class)) ? $class : app($class);
        $ignore = get_class_methods($temp);
        $relationships = [];
        $fileContent = [];
        $scopes = [];
        $canSoftDelete = method_exists($model, 'initializeSoftDeletes');


        foreach (get_class_methods($model) as $method) {
            try {

                $ref = new \ReflectionMethod($model, $method);
                if (!$ref->isStatic() && $ref->isPublic()  && (strpos($method, '__') !== 0)  && !in_array($method, $ignore)) {
                    $params = $ref->getParameters();
                    $process = true;;
                    $getter = "/get[a-zA-Z0-9]+Attribute$/m";
                    $setter = "/set[a-zA-Z0-9]+Attribute$/m";
                    $scope = '/scope[a-zA-Z0-9]/m';

                    if (preg_match($getter, $method)) {
                        $name = Str::snake(str_replace(['get', 'Attribute'], '', $method));
                        $field = $name;
                        $columns[$field] = $this->getColumnMeta([
                            'name' => $name,
                            'is_accessor' => true,
                            'in_filter' => false
                        ]);
                        $columns[$field] = ValidationBuilder::buildRules($columns[$field]);
                        continue;
                    } else if(preg_match($scope, $method)) {
                        $name = lcfirst(str_replace('scope', '', $method));
                        $builtParams = [];

                        array_shift($params);

                        foreach ($params as $aParam) {
                            $builtParams[$aParam->getName()] = [
                                'optional' => $aParam->isOptional(),
                                'type' => $aParam->getType()->getName(),
                            ];
                        }

                        $scopes[$name] = [
                            'name' => $name,
                            'params' => $builtParams
                        ];

                        continue;
                    } else if (preg_match($setter, $method)) {
                        $name = Str::snake(str_replace(['set', 'Attribute'], '', $method));

                        $field = $name;
                        $param = $params[0];

                        $columns[$field] = $this->getColumnMeta([
                            'name' => $name,
                            'is_mutator' => true,
                            'type_name' => ($param->getType()) ? $param->getType()->getName() : 'string',
                            'not_null' => ($param->getType()) ? $param->getType()->allowsNull() : true,
                            'in_create' => true,
                            'in_update' => true,
                            'in_filter' => false
                        ]);
                        $columns[$field] = ValidationBuilder::buildRules($columns[$field]);
                        continue;
                    }

                    foreach ($params as $aParam) {
                        if (!$aParam->isOptional()) {
                            $process = false;
                            break;
                        }
                    }


                    if ($process &&  $appendRelationships) {
                        if (!isset($fileContent[$ref->getFileName()])) {
                            $fileContent[$ref->getFileName()] = array_map(function ($line) {
                                return trim($line) . PHP_EOL;
                            }, explode(PHP_EOL, file_get_contents($ref->getFileName())));
                        }


                        $text = '';
                        for ($line = $ref->getStartLine(); $line < $ref->getEndLine(); $line++) {
                            $text = $fileContent[$ref->getFileName()][$line];
                            if (strpos($text, 'return') === 0) {
                                break;
                            }
                        }
                        $result = (preg_match($this->relationRegex, $text)) ? $model->{$method}() : false;

                        if ($result && $result instanceof Relation) {
                            $snakeName = Str::snake($method);
                           $relationships[$snakeName]  =  $this->parseRelation($result, $columns, [
                                'name' => $snakeName,
                                'label' => $snakeName,
                                'method' => $snakeName
                            ]);
                        }
                    }
                }
            } catch (\Exception $ex) {
                continue;
            }
        }

        return [
            'columns' => $columns,
            'relationships' => $relationships,
            'scopes' => $scopes,
            'can_soft_delete' => $canSoftDelete
        ];
    }
}
