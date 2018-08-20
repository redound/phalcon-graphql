<?php

namespace PhalconGraphQL\Definition;

class EnumTypeValue
{
    protected $_name;
    protected $_description;
    protected $_value;

    public function __construct($name=null, $value=null)
    {
        if($name != null){
            $this->_name = $name;
        }

        if($value !== null){
            $this->_value = $value;
        }
    }

    public function name($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function description($description)
    {
        $this->_description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function value($value)
    {
        $this->_value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public static function factory($name, $value)
    {
        return new static($name, $value);
    }
}
