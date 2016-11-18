<?php

namespace PhalconGraphQL\GraphQL;

use PhalconGraphQL\Definition\InputField;

class InputFieldFactory
{

    public static function build(InputField $inputField, TypeRegistry $typeRegistry)
    {
        $type = $inputField->getType();
        $nonNull = $inputField->getNonNull();
        $isList = $inputField->getIsList();
        $isNonNullList = $inputField->getIsNonNullList();

        return [
            'description' => $inputField->getDescription(),
            'type' => $typeRegistry->resolve($type, $nonNull, $isList, $isNonNullList),
            'defaultValue' => $inputField->getDescription()
        ];
    }
}
