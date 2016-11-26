<?php

namespace PhalconGraphQL\GraphQL;

use PhalconGraphQL\Definition\InputField;

class InputFieldFactory
{
    public static function build(InputField $field, TypeRegistry $typeRegistry)
    {
        $type = $field->getType();
        $nonNull = $field->getNonNull();
        $isList = $field->getIsList();
        $isNonNullList = $field->getIsNonNullList();

        return [
            'description' => $field->getDescription(),
            'type' => $typeRegistry->resolve($type, $nonNull, $isList, $isNonNullList)
        ];
    }
}
