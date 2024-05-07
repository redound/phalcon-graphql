<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Definition\InputObjectType;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\InputObjectType as SchemaInputObjectType;

class InputObjectTypeFactory
{
    public static function build(SchemaInputObjectType $objectType)
    {
        $fields = [];

        /** @var InputField $field */
        foreach ($objectType->getFields() as $field) {
            $fields[] = InputFieldFactory::build($field);
        }

        return new InputObjectTypeDefinitionNode([
            'name' => new NameNode(['value' => $objectType->getName()]),
            'description' => $objectType->getDescription(),
            'fields' => new NodeList($fields),
            'directives' => new NodeList([])
        ]);
    }
}
