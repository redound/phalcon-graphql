<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\ModelField;
use PhalconGraphQL\Definition\Schema;

class ModelMutationFieldGroup extends FieldGroup
{
    protected $_modelClass;

    public function __construct($modelClass)
    {
        $this->_modelClass = $modelClass;
    }

    public function build(Schema $schema, DiInterface $di)
    {
        $modelFields = [];
        $modelFields[] = ModelField::create($this->_modelClass);
        $modelFields[] = ModelField::update($this->_modelClass);
        $modelFields[] = ModelField::delete($this->_modelClass);

        $this->_fields = array_merge($modelFields, $this->_fields);
    }

    /**
     * @return static
     */
    public static function factory($modelClass){

        return new ModelMutationFieldGroup($modelClass);
    }
}