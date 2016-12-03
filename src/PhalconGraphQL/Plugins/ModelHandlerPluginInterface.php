<?php

namespace PhalconGraphQL\Plugins;

use PhalconGraphQL\Definition\Fields\Field;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;


interface ModelHandlerPluginInterface
{
    public function beforeHandle($args, Field $field);
    public function beforeHandleAll($args, Field $field);
    public function beforeHandleFind($args, Field $field);

    public function afterHandle($args, Field $field);
    public function afterHandleAll($data, $response, $args, Field $field);
    public function afterHandleFind($item, $response, $args, Field $field);

    public function modifyQuery(QueryBuilder $query, $args, Field $field);
    public function modifyAllQuery(QueryBuilder $query, $args, Field $field);
    public function modifyFindQuery(QueryBuilder $query, $id, $args, Field $field);

    public function modifyAllResponse($response, $args, Field $field);
    public function modifyFindResponse($response, $args, Field $field);
}