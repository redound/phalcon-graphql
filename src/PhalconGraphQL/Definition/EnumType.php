<?php

namespace PhalconGraphQL\Definition;

use Wittig\PhalconToolkit\Utils;

class EnumType
{
    protected $_name;
    protected $_description;
    protected $_values = [];

    public function __construct($name=null)
    {
        if($name !== null){
            $this->_name = $name;
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

    public function addValue($value)
    {
        $this->_values[] = $value;
        return $this;
    }

    public function value($name, $value)
    {
        $this->addValue(EnumTypeValue::factory($name, $value));
        return $this;
    }

    public function getValues()
    {
        return $this->_values;
    }

    public static function factory($name, $enumClass=null)
    {
        $result = new static($name);

        if($enumClass){

            $options = Utils::getClassConstants($enumClass);

            foreach ($options as $key => $value){
                $result->value($key, $value);
            }
        }

        return $result;
    }
}
