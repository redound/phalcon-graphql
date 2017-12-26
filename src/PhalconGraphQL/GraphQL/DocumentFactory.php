<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\SchemaDefinitionNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use Phalcon\DiInterface;
use PhalconGraphQL\Definition\EnumType;
use PhalconGraphQL\Definition\InputObjectType;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\ObjectTypeGroups\ObjectTypeGroupInterface;
use PhalconGraphQL\Definition\ScalarTypes\DateScalarType;
use PhalconGraphQL\Definition\ScalarTypes\DateTimeScalarType;
use PhalconGraphQL\Definition\ScalarTypes\JsonScalarType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Definition\UnionType;

class DocumentFactory
{
    public static function build(Schema $schema)
    {
        $definitions = [];

        /** @var ScalarType $scalarType */
        foreach ($schema->getScalarTypes() as $scalarType) {
            $definitions[] = new ScalarTypeDefinitionNode(['name' => new NameNode(['value' => $scalarType->name])]);
        }

        /** @var EnumType $enumType */
        foreach ($schema->getEnumTypes() as $enumType) {
            $definitions[] = EnumTypeFactory::build($enumType);
        }

        /** @var ObjectType $objectType */
        foreach ($schema->getObjectTypes() as $objectType) {
            $definitions[] = ObjectTypeFactory::build($schema, $objectType);
        }

        /** @var UnionType $unionType */
        foreach ($schema->getUnionTypes() as $unionType) {
            $definitions[] = UnionTypeFactory::build($schema, $unionType);
        }

        /** @var InputObjectType $inputObjectType */
        foreach ($schema->getInputObjectTypes() as $inputObjectType) {
            $definitions[] = InputObjectTypeFactory::build($inputObjectType);
        }

        return new DocumentNode([
            'definitions' => $definitions
        ]);
    }
}
