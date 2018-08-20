<?php

namespace PhalconGraphQL\Definition\ObjectTypeGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Exception;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;

class ObjectTypeGroup implements ObjectTypeGroupInterface
{
    protected $_objectTypes = [];
    protected $_allowedRoles = [];
    protected $_deniedRoles = [];
    protected $_allowedObjectRoles = [];
    protected $_deniedObjectRoles = [];
    protected $_plugins = [];

    protected $_built = false;

    public function add(ObjectType $objectType){

        $this->_objectTypes[] = $objectType;
        return $this;
    }

    public function allow($roles)
    {
        $this->_allowedRoles = array_merge($this->_allowedRoles, is_array($roles) ? $roles : [$roles]);
        return $this;
    }

    public function deny($roles)
    {
        $this->_deniedRoles = array_merge($this->_deniedRoles, is_array($roles) ? $roles : [$roles]);
        return $this;
    }

    public function allowObject($objectTypeName, $roles)
    {
        $objectRoles = array_key_exists($objectTypeName, $this->_allowedObjectRoles) ? $this->_allowedObjectRoles[$objectTypeName] : [];
        $objectRoles = array_merge($objectRoles, is_array($roles) ? $roles : [$roles]);
        $this->_allowedObjectRoles[$objectTypeName] = $objectRoles;

        return $this;
    }

    public function denyObject($objectTypeName, $roles)
    {
        $objectRoles = array_key_exists($objectTypeName, $this->_deniedObjectRoles) ? $this->_deniedObjectRoles[$objectTypeName] : [];
        $objectRoles = array_merge($objectRoles, is_array($roles) ? $roles : [$roles]);
        $this->_deniedObjectRoles[$objectTypeName] = $objectRoles;

        return $this;
    }

    public function plugin($plugin)
    {
        $this->_plugins[] = $plugin;
        return $this;
    }

    public function build(Schema $schema, DiInterface $di)
    {
        $objectTypes = array_merge($this->_objectTypes, $this->getDefaultObjectTypes($schema, $di));

        /** @var ObjectType $objectType */
        foreach($objectTypes as $objectType){

            $objectType->allow($this->_allowedRoles);
            $objectType->deny($this->_deniedRoles);

            $objectName = $objectType->getName();

            if(array_key_exists($objectName, $this->_allowedObjectRoles)){
                $objectType->allow($this->_allowedObjectRoles[$objectName]);
            }

            if(array_key_exists($objectName, $this->_deniedObjectRoles)){
                $objectType->deny($this->_deniedObjectRoles[$objectName]);
            }

            foreach($this->_plugins as $plugin){
                $objectType->plugin($plugin);
            }
        }

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
    public static function factory($arg=null){

        return new static();
    }
}