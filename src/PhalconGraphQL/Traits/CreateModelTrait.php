<?php

use PhalconGraphQL\Definition\Fields\Field;
use Phalcon\Mvc\Model;
use PhalconApi\Exception;
use PhalconApi\Constants\ErrorCodes;

trait CreateModelTrait
{
    protected function _create($args, Field $field)
    {
        $data = $args['input'];

        $this->_beforeHandle($args, $field);
        $this->_beforeHandleCreate($args, $field);

        if (!$this->_dataValid($data, false, $args, $field)) {
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

    protected function _beforeHandleCreate($args, Field $field)
    {
    }

    protected function _createAllowed($data, $args, Field $field)
    {
        return true;
    }

    protected function _createItem(Model $item, $data, $args, Field $field)
    {
        $this->_beforeAssignData($item, $data, $args, $field);
        $item->assign($data);
        $this->_afterAssignData($item, $data, $args, $field);

        $this->_beforeSave($item, $args, $field);
        $this->_beforeCreate($item, $args, $field);

        $success = $item->create();

        if ($success) {

            $this->_afterCreate($item, $args, $field);
            $this->_afterSave($item, $args, $field);
        }

        return $success ? $item : null;
    }

    protected function _beforeCreate(Model $item, $args, Field $field)
    {
    }

    protected function _afterCreate(Model $item, $args, Field $field)
    {
    }

    protected function _onCreateFailed(Model $item, $data, $args, Field $field)
    {
        throw new Exception(ErrorCodes::DATA_FAILED, 'Unable to create item', [
            'messages' => $this->_getMessages($item->getMessages()),
            'data' => $data,
            'item' => $item->toArray()
        ]);
    }

    protected function _getCreateResponse($createdItem, $data, $args, Field $field)
    {
        return $createdItem;
    }

    protected function _afterHandleCreate(Model $createdItem, $data, $response, $args, Field $field)
    {
    }
}