<?php

declare(strict_types=1);


namespace Kpama\Easybuilder\Lib\Transformer;

interface TransformerInterface {

  public function transform(array $parsedData, bool $appendRelationships = true): array;
}