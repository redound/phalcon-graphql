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
use GraphQL\Type\Schema as GraphQLSchema;

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

    public static function createTypeConfigDecorator(Schema $schema, \Closure $graphqlSchemaProvider)
    {
        return function($typeConfig, $typeDefinitionNode) use ($schema, $graphqlSchemaProvider) {

            $name = $typeConfig['name'];
            $type = $schema->findType($name);
            if(!$type){
                return $typeConfig;
            }

            if($type instanceof EnumType){

                $valueConfigs = $typeConfig['values'];

                // Add enum values
                foreach($type->getValues() as $value){

                    $config = $valueConfigs[$value->getName()];
                    $config['value'] = $value->getValue();

                    $valueConfigs[$value->getName()] = $config;
                }

                $typeConfig['values'] = $valueConfigs;
            }
            else if($type instanceof ScalarType){

                // Add scalar methods
                $typeConfig['serialize'] = [$type, 'serialize'];
                $typeConfig['parseValue'] = [$type, 'parseValue'];
                $typeConfig['parseLiteral'] = [$type, 'parseLiteral'];
            }
            else if($type instanceof UnionType){

                $typeConfig['resolveType'] = self::createResolveType($graphqlSchemaProvider);
            }

            return $typeConfig;
        };
    }

    public static function createResolveType(\Closure $graphqlSchemaProvider)
    {
        return function($value) use ($graphqlSchemaProvider) {

            if($value === null){
                return null;
            }

            $typename = null;
            if(is_array($value)){
                $typename = isset($value['__typename']) ? $value['__typename'] : null;
            }
            else {
                $typename = $value->__typename;
            }

            if(!$typename){
                throw new \Exception('Key __typename needs to be present in response');
            }

            $graphqlSchema = $graphqlSchemaProvider();
            return $graphqlSchema->getType($typename);
        };
    }
}
