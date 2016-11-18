<?php

namespace PhalconGraphQL\Definition;

class InputObjectType
{
    protected $_name;
    protected $_description;
    protected $_handler;
    protected $_fields = [];

    public function __construct($name=null)
    {
        if($name !== null){
            $this->_name = $name;
        }
    }

    public function name($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function description($description)
    {
        $this->_description = $description;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function field(InputField $field)
    {
        $this->_fields[] = $field;
    }

    public function getFields()
    {
        return $this->_fields;
    }

    public static function factory($name=null)
    {
        return new InputObjectType($name);
    }
}
