<?php

use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\ModelField;
use PhalconGraphQL\Constants\Services;
use PhalconApi\Constants\ErrorCodes;
use PhalconGraphQL\Exception;
use Phalcon\Mvc\Model;

trait ModelMutationTrait
{
    protected function _onNotAllowed(array $args, Field $field)
    {
        throw new Exception(ErrorCodes::ACCESS_DENIED, 'Operation is not allowed');
    }

    protected function _onDataInvalid(array $data, array $args, Field $field)
    {
        throw new Exception(ErrorCodes::POST_DATA_INVALID, 'Post-data is invalid');
    }

    protected function _onItemNotFound($id, array $args, Field $field)
    {
        throw new Exception(ErrorCodes::DATA_NOT_FOUND, 'Item was not found');
    }

    protected function _beforeAssignData($item, array $data, array $args, Field $field)
    {
    }

    protected function _afterAssignData($item, array $data, array $args, Field $field)
    {
    }

    protected function _beforeSave($item, array $data, array $args, Field $field)
    {
    }

    protected function _afterSave($item, array $data, array $args, Field $field)
    {
    }

    protected function _beforeHandle(array $args, Field $field)
    {
    }

    protected function _afterHandle(array $args, Field $field)
    {
    }


    /*** GENERAL HOOKS ***/

    protected function _dataValid(array $data, array $args, Field $field)
    {
        return true;
    }

    protected function _saveAllowed(array $data, array $args, Field $field)
    {
        return true;
    }

    protected function _inputAllowed($item, array $data, array $args, Field $field)
    {
        if (!$field instanceof ModelField) {
            return false;
        }

        $fields = $field->getWritableFields($item, $data, $args, $this->getDI());

        return is_array($fields) && !array_diff(array_keys($data), $fields);
    }

    protected function _transformPostData(array $data, array $args, Field $field)
    {
        $result = [];

        foreach ($data as $key => $value) {
            $result[$key] = $this->_transformPostDataValue($key, $value, $data);
        }

        return $result;
    }

    protected function _transformPostDataValue($key, $value, array $data)
    {
        return $value;
    }

    protected function _createModelInstance(Field $field)
    {
        $modelClass = $this->getModel($field);

        return new $modelClass(null, $this->getDI());
    }

    protected function _findModel($id, Field $field)
    {
        if (!is_int($id) && !is_string($id)) {
            throw new Exception(ErrorCodes::POST_DATA_INVALID, 'Invalid ID');
        }

        $modelClass = $this->getModel($field);
        $primaryKey = $this->_getModelPrimaryKey($field);

        $modelInstance = $this->_createModelInstance($field);
        $metadata = $modelInstance->getModelsMetaData();
        $primaryAttribute = $metadata->getPrimaryKeyAttributes($modelInstance)[0];
        $numericAttributes = $metadata->getDataTypesNumeric($modelInstance);

        // Avoid database coercion of arbitrary strings to a numeric key.
        if (!empty($numericAttributes[$primaryAttribute]) && !is_numeric($id)) {
            throw new Exception(ErrorCodes::POST_DATA_INVALID, 'Invalid numeric ID');
        }

        $modelsManager = $this->getDI()->get(Services::MODELS_MANAGER);
        $results = $modelsManager->createBuilder()
            ->from($modelClass)
            ->andWhere('[' . $primaryKey . '] = :mutationId:', ['mutationId' => $id])
            ->limit(1)
            ->getQuery()->execute();

        return count($results) > 0 ? $results->getFirst() : null;
    }

    protected function _getModelPrimaryKey(Field $field)
    {
        /** @var Model $modelInstance */
        $modelInstance = $this->_createModelInstance($field);

        $metadata = $modelInstance->getModelsMetaData();
        $primaryKeys = $metadata->getPrimaryKeyAttributes($modelInstance);

        if (count($primaryKeys) !== 1) {
            throw new \LogicException('Model mutations require a single primary key');
        }

        $primaryKey = $primaryKeys[0];
        $columnMap = $metadata->getColumnMap($modelInstance);

        if (is_array($columnMap)) {
            if (!isset($columnMap[$primaryKey])) {
                throw new \LogicException('Primary key is missing from the column map');
            }

            $primaryKey = $columnMap[$primaryKey];
        }

        if (!is_string($primaryKey) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $primaryKey)) {
            throw new \LogicException('Unsupported primary key attribute');
        }

        return $primaryKey;
    }

    protected function _getMessages($messages)
    {
        $messages = isset($messages) ? $messages : [];

        return array_map(function (\Phalcon\Messages\Message $message) {
            return $message->getMessage();
        }, $messages);
    }
}
