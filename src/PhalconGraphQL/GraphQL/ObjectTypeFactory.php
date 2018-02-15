<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ObjectType;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\FieldGroups\FieldGroupInterface;
use PhalconGraphQL\Definition\ObjectType as SchemaObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Dispatcher;

class ObjectTypeFactory
{
    public static function build(Schema $schema, SchemaObjectType $objectType)
    {
        $fields = [];

        /** @var Field $field */
        foreach ($objectType->getFields() as $field) {
            $fields[] = FieldFactory::build($schema, $objectType, $field);
        }

        return new ObjectTypeDefinitionNode([
            'name' => new NameNode(['value' => $objectType->getName()]),
            'description' => $objectType->getDescription(),
            'fields' => $fields
        ]);
    }
}
