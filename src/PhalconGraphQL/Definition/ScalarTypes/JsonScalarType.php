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

        return json_decode($value);
    }

    public function parseValue($value)
    {
        return json_encode($value);
    }

    public function parseLiteral($ast)
    {
        if ($ast instanceof StringValue) {
            return $ast->value;
        }
        return null;
    }
}