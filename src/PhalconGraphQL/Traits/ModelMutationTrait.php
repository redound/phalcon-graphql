<?php

use PhalconGraphQL\Definition\Fields\Field;
use PhalconApi\Constants\ErrorCodes;
use PhalconApi\Exception;
use Phalcon\Mvc\Model;

trait ModelMutationTrait
{
    protected function _onNotAllowed($args, Field $field)
    {
        throw new Exception(ErrorCodes::ACCESS_DENIED, 'Operation is not allowed');
    }

    protected function _onDataInvalid($data, $args, Field $field)
    {
        throw new Exception(ErrorCodes::POST_DATA_INVALID, 'Post-data is invalid', ['data' => $data]);
    }

    protected function _onItemNotFound($id, $args, Field $field)
    {
        throw new Exception(ErrorCodes::DATA_NOT_FOUND, 'Item was not found', ['id' => $id]);
    }

    protected function _beforeAssignData(Model $item, $data, $args, Field $field)
    {
    }

    protected function _afterAssignData(Model $item, $data, $args, Field $field)
    {
    }

    protected function _beforeSave(Model $item, $args, Field $field)
    {
    }

    protected function _afterSave(Model $item, $args, Field $field)
    {
    }

    protected function _beforeHandle($args, Field $field)
    {
    }

    protected function _afterHandle($args, Field $field)
    {
    }


    /*** GENERAL HOOKS ***/

    protected function _dataValid($data, $isUpdate, $args, Field $field)
    {
        return true;
    }

    protected function _saveAllowed($data, $args, Field $field)
    {
        return true;
    }

    protected function _transformPostData($data, $args, Field $field)
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

    protected function _createModelInstance(Field $field)
    {
        $modelClass = $this->getModel($field);

        return new $modelClass();
    }

    protected function _findModel($id, Field $field)
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

    protected function _getMessages($messages)
    {
        $messages = isset($messages) ? $messages : [];

        return array_map(function (Model\Message $message) {
            return $message->getMessage();
        }, $messages);
    }
}