<?php

use PhalconGraphQL\Definition\Fields\Field;
use Phalcon\Mvc\Model;
use PhalconGraphQL\Exception;
use PhalconApi\Constants\ErrorCodes;

trait CreateModelTrait
{
    protected function _create(array $args, Field $field)
    {
        $data = $args['input'];

        $this->_beforeHandle($args, $field);
        $this->_beforeHandleCreate($args, $field);

        if (!$this->_dataValid($data, $args, $field) || !$this->_createDataValid($data, $args, $field)) {
            return $this->_onDataInvalid($data, $args, $field);
        }

        if (!$this->_saveAllowed($data, $args, $field) || !$this->_createAllowed($data, $args, $field)) {
            return $this->_onNotAllowed($args, $field);
        }

        $data = $this->_transformPostData($data, $args, $field);

        $item = $this->_createModelInstance($field);

        $newItem = $this->_createItem($item, $data, $args, $field);

        if (!$newItem) {
            return $this->_onCreateFailed($item, $data, $args, $field);
        }

        $primaryKey = $this->_getModelPrimaryKey($field);
        $responseData = $this->_findModel($newItem->$primaryKey, $field);

        $response = $this->_getCreateResponse($responseData, $data, $args, $field);

        $this->_afterHandleCreate($newItem, $data, $response, $args, $field);
        $this->_afterHandle($args, $field);

        return $response;
    }

    protected function _createDataValid(array $data, array $args, Field $field)
    {
        return true;
    }

    protected function _beforeHandleCreate(array $args, Field $field)
    {
    }

    protected function _createAllowed(array $data, array $args, Field $field)
    {
        return true;
    }

    protected function _createItem($item, array $data, array $args, Field $field)
    {
        $this->_beforeAssignData($item, $data, $args, $field);
        $this->_beforeAssignCreateData($item, $data, $args, $field);

        $item->assign($data);

        $this->_afterAssignData($item, $data, $args, $field);
        $this->_afterAssignCreateData($item, $data, $args, $field);

        $this->_beforeSave($item, $args, $field);
        $this->_beforeCreate($item, $args, $field);

        $success = $item->create();

        if ($success) {

            $this->_afterCreate($item, $args, $field);
            $this->_afterSave($item, $args, $field);
        }

        return $success ? $item : null;
    }

    protected function _beforeAssignCreateData($item, array $data, array $args, Field $field)
    {
    }

    protected function _afterAssignCreateData($item, array $data, array $args, Field $field)
    {
    }

    protected function _beforeCreate($item, array $args, Field $field)
    {
    }

    protected function _afterCreate($item, array $args, Field $field)
    {
    }

    protected function _onCreateFailed($item, array $data, array $args, Field $field)
    {
        throw new Exception(ErrorCodes::DATA_FAILED, 'Unable to create item', [
            'messages' => $this->_getMessages($item->getMessages()),
            'data' => $data,
            'item' => $item->toArray()
        ]);
    }

    protected function _getCreateResponse($createdItem, array $data, array $args, Field $field)
    {
        return $createdItem;
    }

    protected function _afterHandleCreate($createdItem, array $data, $response, array $args, Field $field)
    {
    }
}