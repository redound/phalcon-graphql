<?php

namespace PhalconGraphQL\GraphQL;

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
            Types::ID => Type::id(),

            Types::DATE => new DateScalarType,
            Types::DATE_TIME => new DateTimeScalarType,
            Types::JSON => new JsonScalarType
        ];

        foreach ($defaultScalarTypes as $name => $type) {
            $typeRegistry->register($name, $type);
        }

        /** @var ScalarType $scalarType */
        foreach($schema->getScalarTypes() as $scalarType) {
            $typeRegistry->register($scalarType->name, $scalarType);
        }

        /** @var EnumType $enumType */
        foreach ($schema->getEnumTypes() as $enumType) {
            $typeRegistry->register($enumType->getName(), EnumTypeFactory::build($enumType));
        }

        $objectTypes = $schema->getObjectTypes();

        /** @var ObjectType $objectType */
        foreach ($objectTypes as $objectType) {
            $typeRegistry->register($objectType->getName(), ObjectTypeFactory::build($dispatcher, $schema, $objectType, $typeRegistry));
        }

        /** @var InputObjectType $inputObjectType */
        foreach ($schema->getInputObjectTypes() as $inputObjectType) {
            $typeRegistry->register($inputObjectType->getName(), InputObjectTypeFactory::build($inputObjectType, $typeRegistry));
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
