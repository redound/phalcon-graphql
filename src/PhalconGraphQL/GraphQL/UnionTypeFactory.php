<?php

namespace PhalconGraphQL\GraphQL;

use GraphQL\Type\Definition\UnionType;
use PhalconGraphQL\Definition\UnionType as SchemaUnionType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Dispatcher;

class UnionTypeFactory
{
    public static function build(Dispatcher $dispatcher, Schema $schema, SchemaUnionType $unionType, TypeRegistry $typeRegistry)
    {
        return new UnionType([
            'name' => $unionType->getName(),
            'description' => $unionType->getDescription(),
            'types' => function() use ($unionType, $dispatcher, $schema, $typeRegistry){

                $types = [];

                foreach ($unionType->getTypes() as $type) {
                    $types[] = $typeRegistry->resolve($type);
                }

                return $types;
            },
            'resolveType' => function($value) use ($typeRegistry) {

                if($value === null){
                    return null;
                }

                if(!isset($value['__typename'])){
                    throw new \Exception('Key __typename needs to be present in response');
                }

                return $typeRegistry->resolve($value['__typename']);
            }
        ]);
    }
}
