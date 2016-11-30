<?php

trait UpdateModelTrait
{
    protected function _update(\PhalconGraphQL\Definition\Fields\Field $field, $data)
    {
        $primaryKey = $this->_getModelPrimaryKey($field);
        $id = isset($data[$primaryKey]) ? $data[$primaryKey] : null;
        if($id === null){
            throw new \PhalconApi\Exception(\PhalconApi\Constants\ErrorCodes::POST_DATA_INVALID, 'No ID found in data (key is ' . $primaryKey . ')', $data);
        }

        $this->_beforeHandleWrite();
        $this->_beforeHandleUpdate($id);

        $item = $this->_findModel($field, $id);

        if (!$item) {
            return $this->_onItemNotFound($id);
        }

        if (!$this->_dataValid($data, true)) {
            return $this->_onDataInvalid($data);
        }

        if (!$this->_saveAllowed($data) || !$this->_updateAllowed($item, $data)) {
            return $this->_onNotAllowed();
        }

        $data = $this->_transformPostData($data);

        $newItem = $this->_updateItem($item, $data);

        if (!$newItem) {
            return $this->_onUpdateFailed($item, $data);
        }

        $responseData = $this->_findModel($field, $newItem->$primaryKey);

        $response = $this->_getUpdateResponse($responseData, $data);

        $this->_afterHandleUpdate($newItem, $data, $response);
        $this->_afterHandleWrite();

        return $response;
    }

    protected function _beforeHandleUpdate($id)
    {
    }

    protected function _updateAllowed(\Phalcon\Mvc\Model $item, $data)
    {
        return true;
    }


    /**
     * @param \Phalcon\Mvc\Model $item
     * @param $data
     *
     * @return \Phalcon\Mvc\Model Updated model
     */
    protected function _updateItem(\Phalcon\Mvc\Model $item, $data)
    {
        $this->_beforeAssignData($item, $data);
        $item->assign($data, null);
        $this->_afterAssignData($item, $data);

        $this->_beforeSave($item);
        $this->_beforeUpdate($item);

        $success = $item->update();

        if ($success) {

            $this->_afterUpdate($item);
            $this->_afterSave($item);
        }

        return $success ? $item : null;
    }

    protected function _beforeUpdate(\Phalcon\Mvc\Model $item)
    {
    }

    protected function _afterUpdate(\Phalcon\Mvc\Model $item)
    {
    }

    protected function _onUpdateFailed(\Phalcon\Mvc\Model $item, $data)
    {
        throw new \PhalconApi\Exception(\PhalconApi\Constants\ErrorCodes::DATA_FAILED, 'Unable to update item', [
            'messages' => $this->_getMessages($item->getMessages()),
            'data' => $data,
            'item' => $item->toArray()
        ]);
    }

    protected function _getUpdateResponse($updatedItem, $data)
    {
        return $updatedItem;
    }

    protected function _afterHandleUpdate(\Phalcon\Mvc\Model $updatedItem, $data, $response)
    {
    }
}