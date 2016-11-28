<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\ModelField;
use PhalconGraphQL\Definition\Schema;

class ModelQueryFieldGroup extends ModelFieldGroup
{
    protected function getDefaultFields(Schema $schema, DiInterface $di)
    {
        return [
            ModelField::all($this->_modelClass),
            ModelField::find($this->_modelClass)
        ];
    }

    /**
     * @return static
     */
    public static function factory($modelClass, $handler=null){

        return new ModelQueryFieldGroup($modelClass, $handler=null);
    }
}