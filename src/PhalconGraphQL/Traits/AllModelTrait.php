<?php

use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;
use PhalconGraphQL\Definition\Fields\Field;

trait AllModelTrait
{
    protected function _all($args, Field $field)
    {
        $this->_invokePlugins($field, 'beforeHandle', [$args, $field]);
        $this->_beforeHandle($args, $field);

        $this->_invokePlugins($field, 'beforeHandleAll', [$args, $field]);
        $this->_beforeHandleAll($args, $field);

        $data = $this->_getAllData($args, $field);

        if (!$this->_allAllowed($data, $args, $field)) {
            return $this->_onNotAllowed($args, $field);
        }

        $response = $this->_getAllResponse($data, $args, $field);
        $response = $this->_invokePlugins($field, 'modifyAllResponse', [$args, $field], $response);

        $this->_invokePlugins($field, 'afterHandleAll', [$data, $response, $args, $field]);
        $this->_afterHandleAll($data, $response, $args, $field);

        $this->_invokePlugins($field, 'afterHandle', [$args, $field]);
        $this->_afterHandle($args, $field);

        return $response;
    }

    protected function _beforeHandleAll($args, Field $field)
    {
    }

    protected function _getAllData($args, Field $field)
    {
        /** @var \Phalcon\Mvc\Model\Manager $modelsManager */
        $modelsManager = $this->di->get(\PhalconGraphQL\Constants\Services::MODELS_MANAGER);
        $model = $this->getModel($field);

        $phqlBuilder = $modelsManager->createBuilder()
            ->from([\PhalconGraphQL\Core::getShortClass($model) => $model]);

        $this->_invokePlugins($field, 'modifyQuery', [$phqlBuilder, $args, $field, false]);
        $this->_modifyQuery($phqlBuilder, $args, $field, false);

        $this->_invokePlugins($field, 'modifyAllQuery', [$phqlBuilder, $args, $field, false]);
        $this->_modifyAllQuery($phqlBuilder, $args, $field);

        return $phqlBuilder->getQuery()->execute();
    }

    protected function _getTotalCount($args, Field $field)
    {
        /** @var \Phalcon\Mvc\Model\Manager $modelsManager */
        $modelsManager = $this->di->get(\PhalconGraphQL\Constants\Services::MODELS_MANAGER);
        $model = $this->getModel($field);

        $alias = \PhalconGraphQL\Core::getShortClass($model);

        $phqlBuilder = $modelsManager->createBuilder()
            ->columns('COUNT(['.$alias.'].[' . $this->_getModelPrimaryKey($field) . ']) as count')
            ->from([$alias => $model]);

        $this->_invokePlugins($field, 'modifyQuery', [$phqlBuilder, $args, $field, true]);
        $this->_modifyQuery($phqlBuilder, $args, $field, true);

        $this->_invokePlugins($field, 'modifyAllQuery', [$phqlBuilder, $args, $field, true]);
        $this->_modifyAllQuery($phqlBuilder, $args, $field, true);

        $results = $phqlBuilder->getQuery()->execute();
        return $this->_getTotalCountResponse($results, $args, $field);
    }

    protected function _getTotalCountResponse($results, $args, Field $field){

        return count($results) > 0 ? (int)$results[0]->count : 0;
    }

    protected function _modifyAllQuery(QueryBuilder $query, $args, Field $field, $count=false)
    {
    }

    protected function _allAllowed($data, $args, Field $field)
    {
        return true;
    }

    protected function _getAllResponse($result, $args, Field $field)
    {
        $model = $this->getModel($field);
        $returnType = $this->schema->findObjectType($field->getType());

        if($returnType->getHandler() == \PhalconGraphQL\Handlers\ListEmbedHandler::class) {

            return \PhalconGraphQL\Responses\ListEmbedResponse::factory($result, $this->_getTotalCount($args, $field));
        }
        else {

            return $result;
        }
    }

    protected function _afterHandleAll($data, $response, $args, Field $field)
    {
    }
}