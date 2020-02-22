<?php

namespace PhalconGraphQL\Plugins;

use PhalconGraphQL\Definition\Fields\Field;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;

interface ModelHandlerPluginInterface
{
    public function beforeHandle(array $args, Field $field);
    public function beforeHandleAll(array $args, Field $field);
    public function beforeHandleFind(array $args, Field $field);
    public function beforeHandleRelation($source, array $args, Field $field);

    public function afterHandle(array $args, Field $field);
    public function afterHandleAll($data, $response, array $args, Field $field);
    public function afterHandleFind($item, $response, array $args, Field $field);
    public function afterHandleRelation($data, $response, $source, array $args, Field $field);


    public function modifyQuery(QueryBuilder $query, array $args, Field $field, $isCount);
    public function modifyAllQuery(QueryBuilder $query, array $args, Field $field, $isCount);
    public function modifyFindQuery(QueryBuilder $query, $id, array $args, Field $field);
    public function modifyRelationOptions($options, $source, array $args, Field $field, $isCount);

    public function modifyAllResponse($response, array $args, Field $field);
    public function modifyFindResponse($response, array $args, Field $field);
    public function modifyRelationResponse($response, $source, array $args, Field $field);
}