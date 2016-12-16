<?php

use PhalconGraphQL\Definition\Fields\Field;
use Phalcon\Mvc\Model;
use PhalconApi\Exception;
use \PhalconApi\Constants\ErrorCodes;

trait RelationModelTrait
{
    protected function _relation($source, $args, Field $field)
    {
        if(!($source instanceof Model)){
            throw new Exception(ErrorCodes::GENERAL_SYSTEM, "Source for relation should be instance of Model");
        }

        $this->_invokePlugins($field, 'beforeHandle', [$args, $field]);
        $this->_beforeHandle($args, $field);

        $this->_invokePlugins($field, 'beforeHandleRelation', [$source, $args, $field]);
        $this->_beforeHandleRelation($source, $args, $field);

        $data = $this->_getRelationData($source, $args, $field);

        if (!$this->_relationAllowed($data, $source, $args, $field)) {
            return $this->_onNotAllowed($args, $field);
        }

        $response = $this->_getRelationResponse($data, $source, $args, $field);
        $response = $this->_invokePlugins($field, 'modifyRelationResponse', [$source, $args, $field], $response);

        $this->_invokePlugins($field, 'afterHandleRelation', [$data, $response, $source, $args, $field]);
        $this->_afterHandleRelation($data, $response, $source, $args, $field);

        $this->_invokePlugins($field, 'afterHandle', [$args, $field]);
        $this->_afterHandle($args, $field);

        return $response;
    }

    protected function _beforeHandleRelation($source, $args, Field $field)
    {

    }

    protected function _getRelationData($source, $args, Field $field)
    {
        $fieldName = $field->getName();

        /** @var Model $model */
        $model = $source;

        $options = [];

        $modifyResult = $this->_modifyRelationOptions($options, $source, $args, $field);
        if ($modifyResult && is_array($modifyResult)) {
            $options = array_merge($options, $modifyResult);
        }

        $options = $this->_invokePlugins($field, 'modifyRelationOptions', [$source, $args, $field], $options);

        return $model->getRelated($fieldName, $options);
    }

    protected function _modifyRelationOptions($options, $source, $args, Field $field)
    {
    }

    protected function _relationAllowed($data, $source, $args, Field $field)
    {
        return true;
    }

    protected function _getRelationResponse($data, $source, $args, Field $field)
    {
        return $data;
    }

    protected function _afterHandleRelation($data, $response, $source, $args, Field $field)
    {

    }
}