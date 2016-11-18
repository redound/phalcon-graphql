<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Type\Definition\Type;
use PhalconGraphQL\Definition\EnumType;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Dispatcher;

class SchemaFactory
{

    public static function build(Dispatcher $dispatcher, Schema $schema)
    {
        $typeRegistry = new TypeRegistry();

        $defaultScalarTypes = [
            Types::STRING => Type::string(),
            Types::INT => Type::int(),
            Types::FLOAT => Type::float(),
            Types::BOOLEAN => Type::boolean(),
            Types::ID => Type::id()
        ];

        foreach ($defaultScalarTypes as $name => $type) {
            $typeRegistry->register($name, $type);
        }

        /** @var EnumType $enumType */
        foreach ($schema->getEnumTypes() as $enumType) {
            $typeRegistry->register($enumType->getName(), EnumTypeFactory::build($enumType));
        }

        /** @var ObjectType $objectType */
        foreach ($schema->getObjectTypes() as $objectType) {
            $typeRegistry->register($objectType->getName(), ObjectTypeFactory::build($dispatcher, $schema, $objectType, $typeRegistry));
        }

        $schemaFields = [];

        if ($typeRegistry->hasType('Query')) {
            $schemaFields['query'] = $typeRegistry->resolve('Query');
        }

        if ($typeRegistry->hasType('Mutation')) {
            $schemaFields['mutation'] = $typeRegistry->resolve('Mutation');
        }

        return new \GraphQL\Schema($schemaFields);
    }
}
