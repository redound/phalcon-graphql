<?php

namespace PhalconGraphQL\Definition\ObjectTypeGroups;

use PhalconGraphQL\Definition\ObjectType;

class ObjectTypeGroup implements ObjectTypeGroupInterface
{
    protected $_types;

    public function add(ObjectType $objectType){

        $this->_types[] = $objectType;
        return $this;
    }

    /**
     * @return ObjectType[]
     */
    public function getObjectTypes()
    {
        return $this->_types;
    }

    /**
     * @return static
     */
    public static function factory(){

        return new ObjectTypeGroup();
    }
}