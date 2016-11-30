<?php

namespace PhalconGraphQL\Handlers;

use PhalconApi\Constants\ErrorCodes;
use PhalconApi\Exception;
use PhalconGraphQL\Definition\Fields\AllModelField;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\FindModelField;

class ModelQueryHandler extends ModelHandler
{
    use \AllModelTrait;
    use \FindModelTrait;

    public function __call($name, $arguments)
    {
        /** @var Field $field */
        list($source, $args, $field) = $arguments;

        if($field instanceof AllModelField){
            return $this->_all($field);
        }
        else if($field instanceof FindModelField){
            return $this->_find($field, $args['id']);
        }

        throw new Exception(ErrorCodes::GENERAL_SYSTEM, 'No handler function found for field ' . $name);
    }
}