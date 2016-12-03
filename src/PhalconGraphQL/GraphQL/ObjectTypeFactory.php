<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\FieldGroups\FieldGroupInterface;
use PhalconGraphQL\Definition\ObjectType as SchemaObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Dispatcher;

class ObjectTypeFactory
{
    public static function build(Dispatcher $dispatcher, Schema $schema, SchemaObjectType $objectType, TypeRegistry $typeRegistry)
    {
        return new ObjectType([
            'name' => $objectType->getName(),
            'description' => $objectType->getDescription(),
            'fields' => function() use ($objectType, $dispatcher, $schema, $typeRegistry){

                $fields = [];

                /** @var Field $field */
                foreach ($objectType->getFields() as $field) {
                    $fields[$field->getName()] = FieldFactory::build($dispatcher, $schema, $objectType, $field, $typeRegistry);
                }

                return $fields;
            }
        ]);
    }
}
