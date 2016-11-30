<?php

namespace PhalconGraphQL\Handlers;

use PhalconApi\Constants\ErrorCodes;
use PhalconApi\Exception;
use PhalconGraphQL\Definition\Field;

class ModelMutationHandler extends ModelHandler
{
    use \CreateModelTrait;
    use \UpdateModelTrait;
    use \DeleteModelTrait;

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
}