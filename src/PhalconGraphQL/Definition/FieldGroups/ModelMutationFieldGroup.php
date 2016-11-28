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

    public function getDefaultFields(Schema $schema, DiInterface $di)
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
    public static function factory($modelClass){

        return new ModelMutationFieldGroup($modelClass);
    }
}