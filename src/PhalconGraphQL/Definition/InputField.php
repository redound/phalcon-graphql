<?php

namespace PhalconGraphQL\Definition;

use Phalcon\DiInterface;

class InputField
{
    protected $_name;
    protected $_type;
    protected $_description;
    protected $_nonNull;
    protected $_isList;
    protected $_isNonNullList;
    protected $_defaultValue;

    protected $_built = false;

    public function __construct($name=null, $type=null, $description=null)
    {
        if($name !== null){
            $this->_name = $name;
        }

        if($type !== null){
            $this->_type = $type;
        }

        if($description !== null){
            $this->_description = $description;
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

    public function build(Schema $schema, DiInterface $di)
    {
        // Empty
        $this->_built = true;
    }


    public static function factory($name=null, $type=null, $description=null)
    {
        return new InputField($name, $type, $description);
    }

    public static function string($name=null, $description=null)
    {
        return self::factory($name, Types::STRING, $description);
    }

    public static function int($name=null, $description=null)
    {
        return self::factory($name, Types::INT, $description);
    }

    public static function float($name=null, $description=null)
    {
        return self::factory($name, Types::FLOAT, $description);
    }

    public static function boolean($name=null, $description=null)
    {
        return self::factory($name, Types::BOOLEAN, $description);
    }

    public static function id($name=null, $description=null)
    {
        return self::factory($name, Types::ID, $description);
    }
}
