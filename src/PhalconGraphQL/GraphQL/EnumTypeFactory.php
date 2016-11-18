<?php

namespace PhalconGraphQL\GraphQL;

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
            $values[$value->getName()] = [
                'value' => $value->getValue(),
                'description' => $value->getDescription()
            ];
        }

        return new EnumType([
            'name' => $enumType->getName(),
            'description' => $enumType->getDescription(),
            'values' => $values
        ]);
    }
}
