<?php

namespace PhalconGraphQL\Plugins;

use Phalcon\DiInterface;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;

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

    public function beforeBuildSchema(Schema $schema, DiInterface $di)
    {

    }

    public function afterBuildSchema(Schema $schema, DiInterface $di)
    {

    }

    public function beforeHandle($args, Field $field)
    {

    }

    public function beforeHandleAll($args, Field $field)
    {

    }

    public function beforeHandleFind($args, Field $field)
    {

    }

    public function afterHandle($args, Field $field)
    {

    }

    public function afterHandleAll($data, $response, $args, Field $field)
    {

    }

    public function afterHandleFind($item, $response, $args, Field $field)
    {

    }

    public function modifyQuery(QueryBuilder $query, $args, Field $field)
    {

    }

    public function modifyAllQuery(QueryBuilder $query, $args, Field $field)
    {

    }

    public function modifyFindQuery(QueryBuilder $query, $id, $args, Field $field)
    {

    }

    public function modifyAllResponse($response, $args, Field $field)
    {

    }

    public function modifyFindResponse($response, $args, Field $field)
    {

    }
}