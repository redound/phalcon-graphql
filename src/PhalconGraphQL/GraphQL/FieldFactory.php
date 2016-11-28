<?php

namespace PhalconGraphQL\GraphQL;

use PhalconGraphQL\Definition\Field;
use PhalconGraphQL\Definition\FieldGroups\FieldGroupInterface;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Dispatcher;

class FieldFactory
{
    public static function build(Dispatcher $dispatcher, Schema $schema, ObjectType $objectType, Field $field, $fieldGroup, TypeRegistry $typeRegistry)
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
            'resolve' => $dispatcher->createResolver($schema, $objectType, $field, $fieldGroup)
        ];
    }
}
