<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\ModelField;
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
            ModelField::create($this->_modelClass),
            ModelField::update($this->_modelClass),
            ModelField::delete($this->_modelClass)
        ];
    }

    /**
     * @return static
     */
    public static function factory($modelClass, $handler=null){

        return new ModelMutationFieldGroup($modelClass, $handler);
    }
}