<?php

use PhalconGraphQL\Definition\Fields\Field;
use PhalconApi\Constants\ErrorCodes;
use PhalconApi\Exception;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;

trait ModelQueryTrait
{
    protected function _onNotAllowed($args, Field $field)
    {
        throw new Exception(ErrorCodes::ACCESS_DENIED, 'Operation is not allowed');
    }

    protected function _modifyQuery(QueryBuilder $query, $args, Field $field)
    {
    }

    protected function _getModelPrimaryKey(Field $field)
    {
        $modelClass = $this->getModel($field);

        /** @var Model $modelInstance */
        $modelInstance = new $modelClass();

        return $modelInstance->getModelsMetaData()->getIdentityField($modelInstance);
    }

    protected function _beforeHandle($args, Field $field)
    {
    }

    protected function _afterHandle($args, Field $field)
    {
    }
}