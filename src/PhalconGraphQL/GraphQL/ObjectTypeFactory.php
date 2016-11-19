<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use PhalconGraphQL\Definition\Field;
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
            'fields' => function () use ($dispatcher, $objectType, $typeRegistry, $schema) {

                $fields = [];

                $handler = $dispatcher->createHandler($objectType, $schema);

                /** @var Field $field */
                foreach ($objectType->getFields() as $field) {
                    $fields[$field->getName()] = FieldFactory::build($dispatcher, $handler, $field, $typeRegistry);
                }

                return $fields;
            }
        ]);
    }
}
