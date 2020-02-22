<?php

namespace PhalconGraphQL\Plugins;

use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\UnionType;

abstract class Plugin implements PluginInterface
{
    /**
     * @var Schema
     */
    protected $schema;

    public function setSchema(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function beforeBuildField(Field $field, ObjectType $objectType, DiInterface $di)
    {

    }

    public function afterBuildField(Field $field, ObjectType $objectType, DiInterface $di)
    {

    }

    public function beforeBuildObjectType(ObjectType $objectType, DiInterface $di)
    {

    }

    public function afterBuildObjectType(ObjectType $objectType, DiInterface $di)
    {

    }

    public function beforeBuildUnionType(UnionType $unionType, DiInterface $di)
    {

    }

    public function afterBuildUnionType(UnionType $unionType, DiInterface $di)
    {

    }

    public function beforeBuildSchema(Schema $schema, DiInterface $di)
    {

    }

    public function afterBuildSchema(Schema $schema, DiInterface $di)
    {

    }

    public function beforeResolve(Schema $schema, ObjectType $objectType, Field $field)
    {

    }

    public function beforeHandle(array $args, Field $field)
    {

    }

    public function beforeHandleAll(array $args, Field $field)
    {

    }

    public function beforeHandleFind(array $args, Field $field)
    {

    }

    public function afterHandle(array $args, Field $field)
    {

    }

    public function afterHandleAll($data, $response, array $args, Field $field)
    {

    }

    public function afterHandleFind($item, $response, array $args, Field $field)
    {

    }

    public function modifyQuery(QueryBuilder $query, array $args, Field $field, $isCount)
    {

    }

    public function modifyAllQuery(QueryBuilder $query, array $args, Field $field, $isCount)
    {

    }

    public function modifyFindQuery(QueryBuilder $query, $id, array $args, Field $field)
    {

    }

    public function modifyAllResponse($response, array $args, Field $field)
    {

    }

    public function modifyFindResponse($response, array $args, Field $field)
    {

    }

    public function beforeHandleRelation($source, array $args, Field $field)
    {

    }

    public function afterHandleRelation($data, $response, $source, array $args, Field $field)
    {

    }

    public function modifyRelationOptions($options, $source, array $args, Field $field, $isCount)
    {

    }

    public function modifyRelationResponse($response, $source, array $args, Field $field)
    {

    }
}