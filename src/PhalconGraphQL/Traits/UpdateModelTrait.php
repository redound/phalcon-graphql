<?php

use PhalconGraphQL\Definition\Fields\Field;
use Phalcon\Mvc\Model;
use PhalconGraphQL\Exception;
use PhalconApi\Constants\ErrorCodes;

trait UpdateModelTrait
{
    protected function _update($args, Field $field)
    {
        $data = $args['input'];

        $primaryKey = $this->_getModelPrimaryKey($field);
        $id = isset($data[$primaryKey]) ? $data[$primaryKey] : null;
        if($id === null){
            throw new Exception(ErrorCodes::POST_DATA_INVALID, 'No ID found in data (key is ' . $primaryKey . ')', $data);
        }

        $this->_beforeHandle($args, $field);
        $this->_beforeHandleUpdate($id, $args, $field);

        $item = $this->_findModel($id, $field);

        if (!$item) {
            return $this->_onItemNotFound($id, $args, $field);
        }

        if (!$this->_dataValid($data, true, $args, $field)) {
            return $this->_onDataInvalid($data, $args, $field);
        }

        if (!$this->_saveAllowed($data, $args, $field) || !$this->_updateAllowed($item, $data, $args, $field)) {
            return $this->_onNotAllowed($args, $field);
        }

        $data = $this->_transformPostData($data, $args, $field);

        $newItem = $this->_updateItem($item, $data, $args, $field);

        if (!$newItem) {
            return $this->_onUpdateFailed($item, $data, $args, $field);
        }

        $responseData = $this->_findModel($newItem->$primaryKey, $field);

        $response = $this->_getUpdateResponse($responseData, $data, $args, $field);

        $this->_afterHandleUpdate($newItem, $data, $response, $args, $field);
        $this->_afterHandle($args, $field);

        return $response;
    }

    protected function _beforeHandleUpdate($id, $args, Field $field)
    {
    }

    protected function _updateAllowed(Model $item, $data, $args, Field $field)
    {
        return true;
    }


    protected function _updateItem(Model $item, $data, $args, Field $field)
    {
        $this->_beforeAssignData($item, $data, $args, $field);
        $item->assign($data, null);
        $this->_afterAssignData($item, $data, $args, $field);

        $this->_beforeSave($item, $args, $field);
        $this->_beforeUpdate($item, $args, $field);

        $success = $item->update();

        if ($success) {

            $this->_afterUpdate($item, $args, $field);
            $this->_afterSave($item, $args, $field);
        }

        return $success ? $item : null;
    }

    protected function _beforeUpdate(Model $item, $args, Field $field)
    {
    }

    protected function _afterUpdate(Model $item, $args, Field $field)
    {
    }

    protected function _onUpdateFailed(Model $item, $data, $args, Field $field)
    {
        throw new Exception(ErrorCodes::DATA_FAILED, 'Unable to update item', [
            'messages' => $this->_getMessages($item->getMessages()),
            'data' => $data,
            'item' => $item->toArray()
        ]);
    }

    protected function _getUpdateResponse($updatedItem, $data, $args, Field $field)
    {
        return $updatedItem;
    }

    protected function _afterHandleUpdate(Model $updatedItem, $data, $response, $args, Field $field)
    {
    }
}