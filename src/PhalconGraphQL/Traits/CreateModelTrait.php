<?php

trait CreateModelTrait
{
    protected function _create(\PhalconGraphQL\Definition\Fields\Field $field, $args)
    {
        $data = $args['input'];

        $this->_beforeHandleWrite();
        $this->_beforeHandleCreate();

        if (!$this->_dataValid($data, false)) {
            return $this->_onDataInvalid($data);
        }

        if (!$this->_saveAllowed($data) || !$this->_createAllowed($data)) {
            return $this->_onNotAllowed();
        }

        $data = $this->_transformPostData($data);

        $item = $this->_createModelInstance($field);

        $newItem = $this->_createItem($item, $data);

        if (!$newItem) {
            return $this->_onCreateFailed($item, $data);
        }

        $primaryKey = $this->_getModelPrimaryKey($field);
        $responseData = $this->_findModel($field, $newItem->$primaryKey);

        $response = $this->_getCreateResponse($responseData, $data);

        $this->_afterHandleCreate($newItem, $data, $response);
        $this->_afterHandleWrite();

        return $response;
    }

    protected function _beforeHandleCreate()
    {
    }

    protected function _createAllowed($data)
    {
        return true;
    }

    /**
     * @param \Phalcon\Mvc\Model $item
     * @param $data
     *
     * @return \Phalcon\Mvc\Model Created item
     */
    protected function _createItem(\Phalcon\Mvc\Model $item, $data)
    {
        $this->_beforeAssignData($item, $data);
        $item->assign($data);
        $this->_afterAssignData($item, $data);

        $this->_beforeSave($item);
        $this->_beforeCreate($item);

        $success = $item->create();

        if ($success) {

            $this->_afterCreate($item);
            $this->_afterSave($item);
        }

        return $success ? $item : null;
    }

    protected function _beforeCreate(\Phalcon\Mvc\Model $item)
    {
    }

    protected function _afterCreate(\Phalcon\Mvc\Model $item)
    {
    }

    protected function _onCreateFailed(\Phalcon\Mvc\Model $item, $data)
    {
        throw new \PhalconApi\Exception(\PhalconApi\Constants\ErrorCodes::DATA_FAILED, 'Unable to create item', [
            'messages' => $this->_getMessages($item->getMessages()),
            'data' => $data,
            'item' => $item->toArray()
        ]);
    }

    protected function _getCreateResponse($createdItem, $data)
    {
        return $createdItem;
    }

    protected function _afterHandleCreate(\Phalcon\Mvc\Model $createdItem, $data, $response)
    {
    }
}