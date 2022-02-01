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
    ];

    $schema = $this->buildObject($parsedData, $schema);

    if ($appendRelationships) {
      foreach ($parsedData['_relationships'] as $name => $data) {
        $schema['properties'][$name] = $this->buildRelation($data);
      }
    }

    return $schema;
  }

  protected function buildObject(array $persedData, array $schema): array
  {
    $schema['type'] = 'object';
    $schema['properties'] = [];

    foreach ($persedData['columns'] as $id => $aColumn) {
      $schema['properties'][$id] = $this->buildColumn($aColumn);
    }

    $schema['required'] = $this->buildRequired($persedData);
    return $schema;
  }

  protected function buildColumn(array $column): array
  {
    $build = [];
    $type = 'string';
    $typeName = $column['type_name'];

    // type
    if (stristr($typeName, 'int')) {
      $type = 'integer';
    } else if ($typeName != 'string') {
      switch ($typeName) {
        case 'decimal':
        case 'dec':
        case 'float':
        case 'fixed':
        case 'number':
        case 'numeric':
        case 'real':
          $type = 'number';
      }
    } else {
    }

    $build['type']  = $type;

    return $build;
  }


  protected function buildRequired(array $parseData): array
  {
    $required = [];

    return $required;
  }

  protected function buildRelation(array $aRelationship): array
  {
    $schema = [];

    if (stristr($aRelationship['type'], 'many')) {
      $schema['type'] = 'array';
      $schema['items'] = $this->buildObject($aRelationship, []);
    } else {
      $schema['type'] = 'object';
      $schema['properties'] = [];
      foreach ($aRelationship['columns'] as $id => $aColumn) {
        $schema['properties'][$id] = $this->buildColumn($aColumn);
      }
    }

    return $schema;
  }
}
