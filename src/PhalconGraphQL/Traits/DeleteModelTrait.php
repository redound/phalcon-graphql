<?php

trait DeleteModelTrait
{
    protected function _delete(\PhalconGraphQL\Definition\Fields\Field $field, $id)
    {
        $this->_beforeHandleWrite();
        $this->_beforeHandleDelete($id);

        $item = $this->_findModel($field, $id);

        if (!$item) {
            return $this->_onItemNotFound($id);
        }

        if (!$this->_deleteAllowed($item)) {
            return $this->_onNotAllowed();
        }

        $success = $this->_deleteItem($item);

        if (!$success) {
            return $this->_onDeleteFailed($item);
        }

        $response = $this->_getDeleteResponse($item);

        $this->_afterHandleDelete($item, $response);
        $this->_afterHandleWrite();

        return $response;
    }

    protected function _beforeHandleDelete($id)
    {
    }

    protected function _deleteAllowed(\Phalcon\Mvc\Model $item)
    {
        return true;
    }

    /**
     * @param \Phalcon\Mvc\Model $item
     *
     * @return bool Delete succeeded/failed
     */
    protected function _deleteItem(\Phalcon\Mvc\Model $item)
    {
        $this->_beforeDelete($item);

        $success = $item->delete();

        if ($success) {
            $this->_afterDelete($item);
        }

        return $success;
    }

    protected function _beforeDelete(\Phalcon\Mvc\Model $item)
    {
    }

    protected function _afterDelete(\Phalcon\Mvc\Model $item)
    {
    }

    protected function _onDeleteFailed(\Phalcon\Mvc\Model $item)
    {
        throw new \PhalconApi\Exception(\PhalconApi\Constants\ErrorCodes::DATA_FAILED, 'Unable to delete item', [
            'messages' => $this->_getMessages($item->getMessages()),
            'item' => $item->toArray()
        ]);
    }

    protected function _getDeleteResponse(\Phalcon\Mvc\Model $deletedItem)
    {
        return true;
    }

    protected function _afterHandleDelete(\Phalcon\Mvc\Model $deletedItem, $response)
    {
    }
}