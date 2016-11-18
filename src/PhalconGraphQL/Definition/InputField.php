<?php

namespace PhalconGraphQL\Definition;

class InputField
{
    protected $_name;
    protected $_type;
    protected $_description;
    protected $_nonNull;
    protected $_isList;
    protected $_isNonNullList;
    protected $_defaultValue;

    public function __construct($name=null, $type=null)
    {
        if($name !== null){
            $this->_name = $name;
        }

        if($type !== null){
            $this->_type = $type;
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

    public function type($type)
    {
        $this->_type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->_type;
    }


    public function nonNull($nonNull = true)
    {
        $this->_nonNull = $nonNull;
        return $this;
    }

    public function getNonNull()
    {
        return $this->_nonNull;
    }

    public function isList($isList = true)
    {
        $this->_isList = $isList;
        return $this;
    }

    public function getIsList()
    {
        return $this->_isList;
    }

    public function isNonNullList($isNonNullList = true)
    {
        $this->_isNonNullList = $isNonNullList;
        return $this;
    }

    public function getIsNonNullList()
    {
        return $this->_isNonNullList;
    }

    public function defaultValue($defaultValue)
    {
        $this->_defaultValue = $defaultValue;
        return $this;
    }

    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }

    public static function factory($name=null, $type=null)
    {
        return new InputField($name, $type);
    }

    public static function string($name=null)
    {
        return self::factory($name, Types::STRING);
    }

    public static function int($name=null)
    {
        return self::factory($name, Types::INT);
    }

    public static function float($name=null)
    {
        return self::factory($name, Types::FLOAT);
    }

    public static function boolean($name=null)
    {
        return self::factory($name, Types::BOOLEAN);
    }

    public static function id($name=null)
    {
        return self::factory($name, Types::ID);
    }
}
