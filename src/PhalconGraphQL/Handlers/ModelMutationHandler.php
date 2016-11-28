<?php

namespace PhalconGraphQL\Handlers;

use Phalcon\Mvc\Model;
use PhalconApi\Constants\ErrorCodes;
use PhalconApi\Exception;
use PhalconGraphQL\Definition\Field;

class ModelMutationHandler extends ModelHandler
{
    public function __call($name, $arguments)
    {
        /** @var Field $field */
        list($source, $args, $field) = $arguments;

        if(stripos($name, 'create') === 0){
            return $this->_create($field, $args['input']);
        }
        else if(stripos($name, 'update') === 0){
            return $this->_update($field, $args['input']);
        }
        else if(stripos($name, 'delete') === 0){
            return $this->_delete($field, $args['id']);
        }

        throw new Exception(ErrorCodes::GENERAL_SYSTEM, 'No handler function found for field ' . $name);
    }


    /*** CREATE ***/

    protected function _create(Field $field, $data)
    {
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
     * @param Model $item
     * @param $data
     *
     * @return Model Created item
     */
    protected function _createItem(Model $item, $data)
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

    protected function _beforeCreate(Model $item)
    {
    }

    protected function _afterCreate(Model $item)
    {
    }

    protected function _onCreateFailed(Model $item, $data)
    {
        throw new Exception(ErrorCodes::DATA_FAILED, 'Unable to create item', [
            'messages' => $this->_getMessages($item->getMessages()),
            'data' => $data,
            'item' => $item->toArray()
        ]);
    }

    protected function _getCreateResponse($createdItem, $data)
    {
        return $createdItem;
    }

    protected function _afterHandleCreate(Model $createdItem, $data, $response)
    {
    }


    /*** UPDATE ***/

    protected function _update(Field $field, $data)
    {
        $primaryKey = $this->_getModelPrimaryKey($field);
        $id = isset($data[$primaryKey]) ? $data[$primaryKey] : null;
        if($id === null){
            throw new Exception(ErrorCodes::POST_DATA_INVALID, 'No ID found in data (key is ' . $primaryKey . ')', $data);
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

    protected function _updateAllowed(Model $item, $data)
    {
        return true;
    }


    /**
     * @param Model $item
     * @param $data
     *
     * @return Model Updated model
     */
    protected function _updateItem(Model $item, $data)
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

    protected function _beforeUpdate(Model $item)
    {
    }

    protected function _afterUpdate(Model $item)
    {
    }

    protected function _onUpdateFailed(Model $item, $data)
    {
        throw new Exception(ErrorCodes::DATA_FAILED, 'Unable to update item', [
            'messages' => $this->_getMessages($item->getMessages()),
            'data' => $data,
            'item' => $item->toArray()
        ]);
    }

    protected function _getUpdateResponse($updatedItem, $data)
    {
        return $updatedItem;
    }

    protected function _afterHandleUpdate(Model $updatedItem, $data, $response)
    {
    }


    /*** DELETE ***/

    protected function _delete(Field $field, $id)
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

    protected function _deleteAllowed(Model $item)
    {
        return true;
    }

    /**
     * @param Model $item
     *
     * @return bool Delete succeeded/failed
     */
    protected function _deleteItem(Model $item)
    {
        $this->_beforeDelete($item);

        $success = $item->delete();

        if ($success) {
            $this->_afterDelete($item);
        }

        return $success;
    }

    protected function _beforeDelete(Model $item)
    {
    }

    protected function _afterDelete(Model $item)
    {
    }

    protected function _onDeleteFailed(Model $item)
    {
        throw new Exception(ErrorCodes::DATA_FAILED, 'Unable to delete item', [
            'messages' => $this->_getMessages($item->getMessages()),
            'item' => $item->toArray()
        ]);
    }

    protected function _getDeleteResponse(Model $deletedItem)
    {
        return true;
    }

    protected function _afterHandleDelete(Model $deletedItem, $response)
    {
    }


    /*** GENERAL EVENT HOOKS ***/

    protected function _onNotAllowed()
    {
        throw new Exception(ErrorCodes::ACCESS_DENIED, 'Operation is not allowed');
    }

    protected function _onDataInvalid($data)
    {
        throw new Exception(ErrorCodes::POST_DATA_INVALID, 'Post-data is invalid', ['data' => $data]);
    }

    protected function _onItemNotFound($id)
    {
        throw new Exception(ErrorCodes::DATA_NOT_FOUND, 'Item was not found', ['id' => $id]);
    }

    protected function _beforeAssignData(Model $item, $data)
    {
    }

    protected function _afterAssignData(Model $item, $data)
    {
    }

    protected function _beforeSave(Model $item)
    {
    }

    protected function _afterSave(Model $item)
    {
    }

    protected function _beforeHandleWrite()
    {
    }

    protected function _afterHandleWrite()
    {
    }


    /*** GENERAL HOOKS ***/

    protected function _dataValid($data, $isUpdate)
    {
        return true;
    }

    protected function _saveAllowed($data)
    {
        return true;
    }

    protected function _transformPostData($data)
    {
        $result = [];

        foreach ($data as $key => $value) {
            $result[$key] = $this->_transformPostDataValue($key, $value, $data);
        }

        return $result;
    }

    protected function _transformPostDataValue($key, $value, $data)
    {
        return $value;
    }

    /**
     * @return Model
     */
    protected function _createModelInstance(Field $field)
    {
        $modelClass = $this->getModel($field);

        return new $modelClass();
    }

    /**
     * @return Model
     */
    protected function _findModel(Field $field, $id)
    {
        $modelClass = $this->getModel($field);

        return $modelClass::findFirst($id);
    }

    protected function _getModelPrimaryKey(Field $field)
    {
        $modelClass = $this->getModel($field);

        /** @var Model $modelInstance */
        $modelInstance = new $modelClass();

        return $modelInstance->getModelsMetaData()->getIdentityField($modelInstance);
    }

    private function _getMessages($messages)
    {
        $messages = isset($messages) ? $messages : [];

        return array_map(function (Model\Message $message) {
            return $message->getMessage();
        }, $messages);
    }
}