<?php

namespace PhalconGraphQL\Definition\FieldGroups;

class ModelFieldGroup extends FieldGroup
{
    protected $_modelClass;

    public function __construct($modelClass, $handler=null)
    {
        $this->_modelClass = $modelClass;

        parent::__construct($handler);
    }

    /**
     * @param mixed $modelClass
     * @return static
     */
    public function model($modelClass)
    {
        $this->_modelClass = $modelClass;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->_modelClass;
    }

    /**
     * @return static
     */
    public static function factory($modelClass, $handler=null){

        return new ModelFieldGroup($modelClass, $handler);
    }
}