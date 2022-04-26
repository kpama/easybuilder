<?php

declare(strict_types=1);


namespace Kpama\Easybuilder\Lib\Transformer;

class Form implements TransformerInterface
{

  public function transform(array $parsedData, bool $appendRelationships = true): array
  {

    $definition = [
      /*[
        'type' => 'layout',
        'flow' => 'horizontal', // vertical,
        'forms' => [],
        'groups' => []
      ] */];

    $fields = [];
    foreach ($parsedData['columns'] as $name => $def) {
      $currentField = null;
      switch ($def['type_name']) {
        case 'bigint':
        case 'int':
          $currentField = $this->buildIntField($def, $parsedData);
          break;
        case 'string':
          $currentField = $this->buildStringField($def, $parsedData);
      }

      if ($currentField) {
        $fields[$name] = [
          "in_create" => $def['in_create'],
          "in_update" => $def['in_update'],
        ] + $currentField;
      }
    }

    $definition[] = [
      'type' => 'form',
      'fields' => $fields
    ];

    return  $definition;
  }


  protected function buildIntField(array $def, array $fullDefinition): array
  {
    $field = [
      'type' => 'input',
      'name' => $def['name'],
      'value' => 0,
      'attributes' => [
        'type' => ($def['in_create'] || $def['in_update']) ? 'number': 'hidden',
        'step' => 1,
        'label' => $def['label'],
      ],
    ];

    return $field;
  }

  protected function buildStringField(array $def, array $fullDefinition): array
  {

    $field = [
      'type' => 'input',
      'name' => $def['name'],
      'value' => '',
      'attributes' => [
        'step' => 1,
        'label' => $def['label'],
      ],
    ];

    return $field;
  }
}
