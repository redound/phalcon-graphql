<?php

namespace PhalconGraphQL\Definition\ScalarTypes;

use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;

class JsonScalarType extends ScalarType
{
    public $name = 'JSON';
    public $description = 'The `JSON` scalar type represents a JSON structure';

    public function __construct()
    {
        parent::__construct();
    }

    public function serialize($value)
    {
        if ($value === null) {
            return null;
        }

        return is_string($value) ? json_decode($value) : $value;
    }

    public function parseValue($value)
    {
        return !is_string($value) ? json_encode($value) : $value;
    }

    public function parseLiteral($ast)
    {
        if ($ast instanceof StringValue) {
            return $this->parseValue($ast->value);
        }

        return null;
    }
}