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

    public function build(Schema $schema, DiInterface $di)
    {
        $modelFields = [];
        $modelFields[] = ModelField::all($this->_modelClass);
        $modelFields[] = ModelField::find($this->_modelClass);

        $this->_fields = array_merge($modelFields, $this->_fields);
    }

    /**
     * @return static
     */
    public static function factory($modelClass){

        return new ModelQueryFieldGroup($modelClass);
    }
}