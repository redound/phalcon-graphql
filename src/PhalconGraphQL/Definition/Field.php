<?php

namespace PhalconGraphQL\Definition;

use PhalconGraphQL\Resolvers\EmptyResolver;

class Field
{
    protected $_name;
    protected $_description;
    protected $_type;
    protected $_nonNull;
    protected $_isList;
    protected $_isNonNullList;
    protected $_resolvers = [];
    protected $_args = [];

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

    public function embed(){

        $this->_type = $this->_isList ? Types::connection($this->_type) : Types::edge($this->_type);
        $this->_isList = false;

        return $this;
    }

    public function nonNull($nonNull = true)
    {
        $this->_nonNull = $nonNull;
        return $this;
    }

    public function null($null = true)
    {
        $this->_nonNull = !$null;
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

    public function resolver($resolver)
    {
        $this->_resolvers[] = $resolver;
        return $this;
    }

    public function getResolvers()
    {
        return $this->_resolvers;
    }

    public function arg(InputField $inputField)
    {
        $this->_args[] = $inputField;
        return $this;
    }

    public function getArgs()
    {
        return $this->_args;
    }

    public static function factory($name=null, $type=null, $description=null)
    {
        return new Field($name, $type, $description);
    }

    public static function listFactory($name=null, $type=null, $description=null)
    {
        return self::factory($name, $type, $description)->isList();
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


    public static function viewer()
    {
        return self::factory('viewer', Types::VIEWER)
            ->nonNull()
            ->resolver(EmptyResolver::class);
    }
}
