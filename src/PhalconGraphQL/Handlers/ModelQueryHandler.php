<?php

namespace PhalconGraphQL\Handlers;

use PhalconApi\Constants\ErrorCodes;
use PhalconGraphQL\Exception;
use PhalconGraphQL\Definition\Fields\AllModelField;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\FindModelField;
use PhalconGraphQL\Definition\Fields\RelationModelField;

class ModelQueryHandler extends ModelHandler
{
    use \ModelQueryTrait;
    use \RelationModelTrait;
    use \AllModelTrait;
    use \FindModelTrait;

    public function __call($name, $arguments)
    {
        /** @var Field $field */
        list($source, $args, $field) = $arguments;

        if($field instanceof AllModelField){
            return $this->_all($args, $field);
        }
        else if($field instanceof FindModelField){
            return $this->_find($args, $field);
        }

        throw new Exception(ErrorCodes::GENERAL_SYSTEM, 'No handler function found for field ' . $name);
    }
}