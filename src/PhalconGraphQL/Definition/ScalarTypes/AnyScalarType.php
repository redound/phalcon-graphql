<?php

namespace PhalconGraphQL\Definition\ScalarTypes;

use GraphQL\Type\Definition\ScalarType;

class AnyScalarType extends ScalarType
{
    public $name = 'Any';
    public $description = 'The `Any` scalar type represents any value';

    public function serialize($value)
    {
        return $value;
    }

    public function parseValue($value)
    {
        return $value;
    }

    public function parseLiteral($valueNode, array $variables = null)
    {
        return $valueNode->value;
    }
}