<?php

namespace PhalconGraphQL\Handlers;

use PhalconApi\Exception;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\FieldGroups\ModelFieldGroup;
use PhalconGraphQL\Definition\Fields\ModelField;

class ModelHandler extends Handler
{
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
}