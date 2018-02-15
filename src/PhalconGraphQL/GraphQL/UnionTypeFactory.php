<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Type\Definition\UnionType;
use PhalconGraphQL\Definition\UnionType as SchemaUnionType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Dispatcher;

class UnionTypeFactory
{
    public static function build(Schema $schema, SchemaUnionType $unionType)
    {
        $types = [];

        foreach ($unionType->getTypes() as $type) {
            $types[] = TypeUtils::node($type);
        }

        return new UnionTypeDefinitionNode([
            'name' => new NameNode(['value' => $unionType->getName()]),
            'description' => $unionType->getDescription(),
            'types' => $types
        ]);
    }
}
