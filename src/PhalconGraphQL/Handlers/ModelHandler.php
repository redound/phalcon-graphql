<?php

namespace PhalconGraphQL\Handlers;

use PhalconApi\Exception;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\ModelField;
use PhalconGraphQL\Definition\Fields\RelationModelField;

class ModelHandler extends Handler
{
    use \ModelQueryTrait;
    use \RelationModelTrait;

    protected function getModel(Field $field){

        $model = null;

        if($field instanceof ModelField){
            $model = $field->getModel();
        }

        if(!$model){
            throw new Exception('No model found for handler');
        }

        return $model;
    }

    public function __call($name, $arguments)
    {
        /** @var Field $field */
        list($source, $args, $field) = $arguments;

        if($field instanceof RelationModelField){
            return $this->_relation($source, $args, $field);
        }

        return parent::__call($name, $arguments);
    }
}