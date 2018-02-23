<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NonNullTypeNode;

class TypeUtils
{
    public static function node($name, $nonNull = false, $isList = false, $isNonNullList = false)
    {
        $nameNode = new NameNode(['value' => $name]);
        $type = new NamedTypeNode(['name' => $nameNode]);

        if ($isList) {

            if ($isNonNullList) {
                $type = new NonNullTypeNode(['type' => $type]);
            }

            $type = new ListTypeNode(['type' => $type]);
        }

        if ($nonNull) {
            $type = new NonNullTypeNode(['type' => $type]);
        }

        return $type;
    }
}