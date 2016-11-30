<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\DiInterface;
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
            CreateModelField::factory($this->_modelClass)->clearResolvers(),
            UpdateModelField::factory($this->_modelClass)->clearResolvers(),
            DeleteModelField::factory($this->_modelClass)->clearResolvers()
        ];
    }

    /**
     * @return static
     */
    public static function factory($modelClass, $handler=null){

        return new ModelMutationFieldGroup($modelClass, $handler);
    }
}