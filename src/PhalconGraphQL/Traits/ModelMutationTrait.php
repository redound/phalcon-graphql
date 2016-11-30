<?php

trait ModelMutationTrait
{
    protected function _onNotAllowed()
    {
        throw new \PhalconApi\Exception(\PhalconApi\Constants\ErrorCodes::ACCESS_DENIED, 'Operation is not allowed');
    }

    protected function _onDataInvalid($data)
    {
        throw new \PhalconApi\Exception(\PhalconApi\Constants\ErrorCodes::POST_DATA_INVALID, 'Post-data is invalid', ['data' => $data]);
    }

    protected function _onItemNotFound($id)
    {
        throw new \PhalconApi\Exception(\PhalconApi\Constants\ErrorCodes::DATA_NOT_FOUND, 'Item was not found', ['id' => $id]);
    }

    protected function _beforeAssignData(\Phalcon\Mvc\Model $item, $data)
    {
    }

    protected function _afterAssignData(\Phalcon\Mvc\Model $item, $data)
    {
    }

    protected function _beforeSave(\Phalcon\Mvc\Model $item)
    {
    }

    protected function _afterSave(\Phalcon\Mvc\Model $item)
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
     * @return \Phalcon\Mvc\Model
     */
    protected function _createModelInstance(\PhalconGraphQL\Definition\Fields\Field $field)
    {
        $modelClass = $this->getModel($field);

        return new $modelClass();
    }

    /**
     * @return \Phalcon\Mvc\Model
     */
    protected function _findModel(\PhalconGraphQL\Definition\Fields\Field $field, $id)
    {
        $modelClass = $this->getModel($field);

        return $modelClass::findFirst($id);
    }

    protected function _getModelPrimaryKey(\PhalconGraphQL\Definition\Fields\Field $field)
    {
        $modelClass = $this->getModel($field);

        /** @var \Phalcon\Mvc\Model $modelInstance */
        $modelInstance = new $modelClass();

        return $modelInstance->getModelsMetaData()->getIdentityField($modelInstance);
    }

    private function _getMessages($messages)
    {
        $messages = isset($messages) ? $messages : [];

        return array_map(function (\Phalcon\Mvc\Model\Message $message) {
            return $message->getMessage();
        }, $messages);
    }
}