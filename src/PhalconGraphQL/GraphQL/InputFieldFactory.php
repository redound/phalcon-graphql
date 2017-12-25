<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NameNode;
use PhalconGraphQL\Definition\InputField;

class InputFieldFactory
{
    public static function build(InputField $field)
    {
        $type = $field->getType();
        $nonNull = $field->getNonNull();
        $isList = $field->getIsList();
        $isNonNullList = $field->getIsNonNullList();

        return new InputValueDefinitionNode([
            'name' => new NameNode(['value' => $field->getName()]),
            'description' => $field->getDescription(),
            'type' => TypeUtils::node($type, $nonNull, $isList, $isNonNullList)
        ]);
    }
}
