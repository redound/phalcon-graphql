<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\Di\DiInterface;
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
            AllModelField::factory($this->_modelClass),
            FindModelField::factory($this->_modelClass)
        ];
    }
}