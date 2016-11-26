<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Type\Definition\InputObjectType;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\InputObjectType as SchemaInputObjectType;

class InputObjectTypeFactory
{
    public static function build(SchemaInputObjectType $objectType, TypeRegistry $typeRegistry)
    {
        return new InputObjectType([
            'name' => $objectType->getName(),
            'description' => $objectType->getDescription(),
            'fields' => function () use ($objectType, $typeRegistry) {

                $fields = [];

                /** @var InputField $field */
                foreach ($objectType->getFields() as $field) {
                    $fields[$field->getName()] = InputFieldFactory::build($field, $typeRegistry);
                }

                return $fields;
            }
        ]);
    }
}
