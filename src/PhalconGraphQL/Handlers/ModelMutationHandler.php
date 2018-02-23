<?php

namespace PhalconGraphQL\Handlers;

use PhalconApi\Constants\ErrorCodes;
use PhalconGraphQL\Exception;
use PhalconGraphQL\Definition\Fields\CreateModelField;
use PhalconGraphQL\Definition\Fields\DeleteModelField;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\UpdateModelField;

class ModelMutationHandler extends ModelHandler
{
    use \ModelMutationTrait;
    use \CreateModelTrait;
    use \UpdateModelTrait;
    use \DeleteModelTrait;

    public function __call($name, $arguments)
    {
        /** @var Field $field */
        list($source, $args, $field) = $arguments;

        if($field instanceof CreateModelField){
            return $this->_create($args, $field);
        }
        else if($field instanceof UpdateModelField){
            return $this->_update($args, $field);
        }
        else if($field instanceof DeleteModelField){
            return $this->_delete($args, $field);
        }

        throw new Exception(ErrorCodes::GENERAL_SYSTEM, 'No handler function found for field ' . $name);
    }
}