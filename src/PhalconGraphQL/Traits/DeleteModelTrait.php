<?php

use PhalconGraphQL\Definition\Fields\Field;
use Phalcon\Mvc\Model;
use PhalconGraphQL\Exception;
use PhalconApi\Constants\ErrorCodes;

trait DeleteModelTrait
{
    protected function _delete(array $args, Field $field)
    {
        $id = $args['id'];

        $this->_beforeHandle($args, $field);
        $this->_beforeHandleDelete($id, $args, $field);

        $item = $this->_findModel($id, $field);

        if (!$item) {
            return $this->_onItemNotFound($id, $args, $field);
        }

        if (!$this->_deleteAllowed($item, $args, $field)) {
            return $this->_onNotAllowed($args, $field);
        }

        $success = $this->_deleteItem($item, $args, $field);

        if (!$success) {
            return $this->_onDeleteFailed($item, $args, $field);
        }

        $response = $this->_getDeleteResponse($item, $args, $field);

        $this->_afterHandleDelete($item, $response, $args, $field);
        $this->_afterHandle($args, $field);

        return $response;
    }

    protected function _beforeHandleDelete($id, array $args, Field $field)
    {
    }

    protected function _deleteAllowed($item, array $args, Field $field)
    {
        return true;
    }

    protected function _deleteItem($item, array $args, Field $field)
    {
        $this->_beforeDelete($item, $args, $field);

        $success = $item->delete();

        if ($success) {
            $this->_afterDelete($item, $args, $field);
        }

        return $success;
    }

    protected function _beforeDelete($item, array $args, Field $field)
    {
    }

    protected function _afterDelete($item, array $args, Field $field)
    {
    }

    protected function _onDeleteFailed($item, array $args, Field $field)
    {
        throw new Exception(ErrorCodes::DATA_FAILED, 'Unable to delete item', [
            'messages' => $this->_getMessages($item->getMessages()),
            'item' => $item->toArray()
        ]);
    }

    protected function _getDeleteResponse($deletedItem, array $args, Field $field)
    {
        return true;
    }

    protected function _afterHandleDelete($deletedItem, $response, array $args, Field $field)
    {
    }
}