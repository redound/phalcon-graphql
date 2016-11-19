<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Type\Definition\Type;
use Phalcon\DiInterface;
use PhalconGraphQL\Definition\EnumType;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\ObjectTypeGroups\ObjectTypeGroupInterface;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Dispatcher;

class SchemaFactory
{
    public static function build(Dispatcher $dispatcher, Schema $schema, DiInterface $di)
    {
        $schema->build($di);

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

        $objectTypes = $schema->getObjectTypes();

        /** @var ObjectTypeGroupInterface $objectTypeGroup */
        foreach($schema->getObjectTypeGroups() as $objectTypeGroup){
            $objectTypes = array_merge($objectTypes, $objectTypeGroup->getObjectTypes());
        }

        /** @var ObjectType $objectType */
        foreach ($objectTypes as $objectType) {
            $typeRegistry->register($objectType->getName(), ObjectTypeFactory::build($dispatcher, $schema, $objectType, $typeRegistry));
        }

        $schemaFields = [];

        if ($typeRegistry->hasType(Types::QUERY)) {
            $schemaFields['query'] = $typeRegistry->resolve(Types::QUERY);
        }

        if ($typeRegistry->hasType(Types::MUTATION)) {
            $schemaFields['mutation'] = $typeRegistry->resolve(Types::MUTATION);
        }

        return new \GraphQL\Schema($schemaFields);
    }
}
