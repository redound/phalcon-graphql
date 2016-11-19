<?php

namespace PhalconGraphQL\Definition\ObjectTypeGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;

class ObjectTypeGroup implements ObjectTypeGroupInterface
{
    protected $_types;

    public function add(ObjectType $objectType){

        $this->_types[] = $objectType;
        return $this;
    }

    public function build(Schema $schema, DiInterface $di)
    {
        // Empty
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