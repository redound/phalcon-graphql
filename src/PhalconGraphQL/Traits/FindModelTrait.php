<?php

use Phalcon\Mvc\Model\Query\BuilderInterface as QueryBuilder;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconApi\Exception;
use PhalconApi\Constants\ErrorCodes;

trait FindModelTrait
{
    public function _find($args, Field $field)
    {
        $id = $args['id'];

        $this->_invokePlugins($field, 'beforeHandle', [$args, $field]);
        $this->_beforeHandle($args, $field);

        $this->_invokePlugins($field, 'beforeHandleFind', [$args, $field]);
        $this->_beforeHandleFind($id, $args, $field);

        $item = $this->_getFindData($id, $args, $field);

        if (!$item) {
            return $this->_onItemNotFound($id, $args, $field);
        }

        if (!$this->_findAllowed($id, $item, $args, $field)) {
            return $this->_onNotAllowed($args, $field);
        }

        $response = $this->_getFindResponse($item, $args, $field);
        $response = $this->_invokePlugins($field, 'modifyFindResponse', [$args, $field], $response);

        $this->_invokePlugins($field, 'afterHandleFind', [$item, $response, $args, $field]);
        $this->_afterHandleFind($item, $response, $args, $field);

        $this->_invokePlugins($field, 'afterHandle', [$args, $field]);
        $this->_beforeHandle($args, $field);

        return $response;
    }

    protected function _beforeHandleFind($id, $args, Field $field)
    {
    }

    protected function _getFindData($id, $args, Field $field)
    {
        /** @var \Phalcon\Mvc\Model\Manager $modelsManager */
        $modelsManager = $this->di->get(\PhalconGraphQL\Constants\Services::MODELS_MANAGER);
        $model = $this->getModel($field);
        $modelAlias = \PhalconGraphQL\Core::getShortClass($model);

        $phqlBuilder = $modelsManager->createBuilder()
            ->from([$modelAlias => $model])
            ->andWhere('[' . $modelAlias . '].[' . $this->_getModelPrimaryKey($field) . '] = :id:',
                ['id' => $id])
            ->limit(1);

        $this->_invokePlugins($field, 'modifyQuery', [$phqlBuilder, $args, $field]);
        $this->_modifyQuery($phqlBuilder, $args, $field);

        $this->_invokePlugins($field, 'modifyFindQuery', [$phqlBuilder, $id, $args, $field]);
        $this->_modifyFindQuery($phqlBuilder, $id, $args, $field);

        $results = $phqlBuilder->getQuery()->execute();

        return count($results) >= 1 ? $results->getFirst() : null;
    }

    protected function _modifyFindQuery(QueryBuilder $query, $id, $args, Field $field)
    {
    }

    /*** ERROR HOOKS ***/

    protected function _onItemNotFound($id, $args, Field $field)
    {
        throw new Exception(ErrorCodes::DATA_NOT_FOUND, 'Item was not found', ['id' => $id]);
    }

    protected function _findAllowed($id, $item, $args, Field $field)
    {
        return true;
    }

    protected function _getFindResponse($item, $args, Field $field)
    {
        return $item;
    }

    protected function _afterHandleFind($item, $response, $args, Field $field)
    {
    }
}