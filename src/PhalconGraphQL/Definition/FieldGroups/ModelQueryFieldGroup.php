<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\ModelField;
use PhalconGraphQL\Definition\Schema;

class ModelQueryFieldGroup extends FieldGroup
{
    protected $_modelClass;

    public function __construct($modelClass)
    {
        $this->_modelClass = $modelClass;
    }

    public function getDefaultFields(Schema $schema, DiInterface $di)
    {
        return [
            ModelField::all($this->_modelClass),
            ModelField::find($this->_modelClass)
        ];
    }

    /**
     * @return static
     */
    public static function factory($modelClass){

        return new ModelQueryFieldGroup($modelClass);
    }
}