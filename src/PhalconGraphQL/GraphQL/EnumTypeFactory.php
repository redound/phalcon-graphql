<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Type\Definition\EnumType;
use PhalconGraphQL\Definition\EnumType as SchemaEnumType;
use PhalconGraphQL\Definition\EnumTypeValue;

class EnumTypeFactory
{

    public static function build(SchemaEnumType $enumType)
    {
        $values = [];

        /** @var EnumTypeValue $value */
        foreach ($enumType->getValues() as $value) {

            $values[] = new EnumValueDefinitionNode([
                'name' => new NameNode(['value' => $value->getName()]),
                'description' => $value->getDescription()
            ]);
        }

        return new EnumTypeDefinitionNode([
            'name' => new NameNode(['value' => $enumType->getName()]),
            'description' => $enumType->getDescription(),
            'values' => $values
        ]);
    }
}
