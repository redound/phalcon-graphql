<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\FieldGroups\FieldGroupInterface;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Dispatcher;

class FieldFactory
{
    public static function build(Schema $schema, ObjectType $objectType, Field $field)
    {
        $type = $field->getType();
        $nonNull = $field->getNonNull();
        $isList = $field->getIsList();
        $isNonNullList = $field->getIsNonNullList();

        $args = [];

        /** @var InputField $inputField */
        foreach ($field->getArgs() as $inputField) {
            $args[] = InputFieldFactory::build($inputField);
        }

        $fieldDefinitionNodeArray = [
            'name' => new NameNode(['value' => $field->getName()]),
            'description' => $field->getDescription(),
            'type' => TypeUtils::node($type, $nonNull, $isList, $isNonNullList),
            'arguments' => new NodeList($args),
            'directives' => new NodeList([]),
        ];

        return new FieldDefinitionNode($fieldDefinitionNodeArray);
    }
}
