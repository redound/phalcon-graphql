<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\AllModelField;
use PhalconGraphQL\Definition\Fields\FindModelField;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Handlers\ModelQueryHandler;

class ModelQueryFieldGroup extends ModelFieldGroup
{
    protected function getDefaultHandler()
    {
        return ModelQueryHandler::class;
    }

    protected function getDefaultFields(Schema $schema, DiInterface $di)
    {
        return [
            AllModelField::factory($this->_modelClass)->clearResolvers(),
            FindModelField::factory($this->_modelClass)->clearResolvers()
        ];
    }

    /**
     * @return static
     */
    public static function factory($modelClass, $handler=null){

        return new ModelQueryFieldGroup($modelClass, $handler=null);
    }
}