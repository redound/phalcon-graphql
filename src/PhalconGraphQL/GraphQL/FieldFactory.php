<?php

namespace PhalconGraphQL\GraphQL;

use PhalconGraphQL\Definition\Field;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Dispatcher;

class FieldFactory
{
    public static function build(Dispatcher $dispatcher, $handler, Field $field, TypeRegistry $typeRegistry)
    {
        $type = $field->getType();
        $nonNull = $field->getNonNull();
        $isList = $field->getIsList();
        $isNonNullList = $field->getIsNonNullList();

        $args = [];

        /** @var InputField $inputField */
        foreach ($field->getArgs() as $inputField) {

            $args[$inputField->getName()] = InputFieldFactory::build($inputField, $typeRegistry);
        }

        return [
            'description' => $field->getDescription(),
            'type' => $typeRegistry->resolve($type, $nonNull, $isList, $isNonNullList),
            'args' => $args,
            'resolve' => $dispatcher->createResolver($handler, $field)
        ];
    }
}
