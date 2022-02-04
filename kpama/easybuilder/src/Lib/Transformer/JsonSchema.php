<?php

declare(strict_types=1);


namespace Kpama\Easybuilder\Lib\Transformer;

class JsonSchema implements TransformerInterface
{
  public function transform(array $parsedData,  bool $appendRelationships = true): array
  {
    $schema = [
      '$schema' => 'https://json-schema.org/draft/2020-12/schema',
      '$id' => '',
      'required' => [],
    ];

    $schema = $this->buildObject($parsedData, $schema);

    if ($appendRelationships) {
      foreach ($parsedData['relationships'] as $name => $data) {
        $schema['properties'][$name] = $this->buildRelation($data, $parsedData);
      }
    }

    return $schema;
  }

  protected function buildObject(array $persedData, array $schema, array $parentRecord = [] ): array
  {
    $schema['type'] = 'object';
    $schema['properties'] = [];

    foreach ($persedData['columns'] as $id => $aColumn) {
      $schema['properties'][$id] = $this->buildColumn($aColumn);

        if($aColumn['not_null'] && !$aColumn['is_primary'] && !$aColumn['is_foreign_key']) {
          $schema['required'][] = $id;
        }
    }

    return $schema;
  }

  protected function buildColumn(array $column): array
  {
    $typeName = $column['type_name'];

    $build = [
      'type' => $typeName
    ];

    // type
    if (stristr($typeName, 'int')) {
        $type = 'integer';
        $build['type']  = $type;
    } else if($typeName == 'string') {
      $build = $this->buildStringType($column);
    } else if(in_array($typeName, ['date', 'datetime'])) {
      $build = $this->buildDateType($column);
    } else if ($typeName != 'string') {
      switch ($typeName) {
        case 'decimal':
        case 'dec':
        case 'float':
        case 'fixed':
        case 'number':
        case 'numeric':
        case 'real':
          $build['type']  = 'number';
      }
    }

    return $build;
  }


  protected function buildRelation(array $aRelationship, array $parentRecord): array
  {
    $schema = [];

    if (stristr($aRelationship['type'], 'many')) {
      $schema['type'] = 'array';
      $schema['items'] = $this->buildObject($aRelationship, [], $parentRecord);
    } else {
      $schema['type'] = 'object';
      $schema['properties'] = [];
      foreach ($aRelationship['columns'] as $id => $aColumn) {
        $schema['properties'][$id] = $this->buildColumn($aColumn, $parentRecord);
      }
    }

    return $schema;
  }

  protected function buildStringType(array $column): array
  {
    $build = [
      'type' => 'string'
    ];

    if($column['not_null']) {
      $build['minLength'] = (isset($column['min_length'])) ?$column['min_length']: 1;
      $build['maxLength'] = $column['length'];
    }


    return $build;
  }

  protected function buildDateType(array $column): array
  {
    $build = [
      'type' => 'string'
    ];

    switch($column['type_name']) {
      case 'datetime':
        $build['format'] = 'date-time'; 
        break;
      case 'date':
        $build['format'] = 'date'; 
        break;
    }

    return $build;
  }
}
