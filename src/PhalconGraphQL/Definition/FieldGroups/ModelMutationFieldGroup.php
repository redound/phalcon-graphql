<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\Di\DiInterface;
use PhalconGraphQL\Definition\Fields\CreateModelField;
use PhalconGraphQL\Definition\Fields\DeleteModelField;
use PhalconGraphQL\Definition\Fields\UpdateModelField;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Handlers\ModelMutationHandler;

class ModelMutationFieldGroup extends ModelFieldGroup
{
    protected function getDefaultHandler()
    {
        return ModelMutationHandler::class;
    }

    protected function getDefaultFields(Schema $schema, DiInterface $di)
    {
        return [
            CreateModelField::factory($this->_modelClass),
            UpdateModelField::factory($this->_modelClass),
            DeleteModelField::factory($this->_modelClass)
        ];
    }
}