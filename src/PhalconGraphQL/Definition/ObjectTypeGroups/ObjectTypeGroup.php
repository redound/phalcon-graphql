<?php

namespace PhalconGraphQL\Definition\ObjectTypeGroups;

use Phalcon\DiInterface;
use PhalconApi\Exception;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;

class ObjectTypeGroup implements ObjectTypeGroupInterface
{
    protected $_objectTypes = [];
    protected $_built = false;

    public function add(ObjectType $objectType){

        $this->_objectTypes[] = $objectType;
        return $this;
    }

    public function build(Schema $schema, DiInterface $di)
    {
        $objectTypes = array_merge($this->_objectTypes, $this->getDefaultObjectTypes($schema, $di));

        $this->_objectTypes = $objectTypes;
        $this->_built = true;
    }

    protected function getDefaultObjectTypes(Schema $schema, DiInterface $di)
    {
        // Override
        return [];
    }

    /**
     * @return ObjectType[]
     * @throws Exception
     */
    public function getObjectTypes()
    {
        if(!$this->_built){
            throw new Exception("Unable to get object types from embedded object type, not built yet");
        }

        return $this->_objectTypes;
    }

    /**
     * @return static
     */
    public static function factory(){

        return new ObjectTypeGroup();
    }
}