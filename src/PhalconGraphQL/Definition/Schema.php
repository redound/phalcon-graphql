<?php

namespace PhalconGraphQL\Definition;

use Phalcon\DiInterface;
use PhalconApi\Constants\ErrorCodes;
use PhalconApi\Exception;
use PhalconGraphQL\Definition\ObjectTypeGroups\ObjectTypeGroupInterface;

class Schema
{
    const EMBED_MODE_NONE = 0;
    const EMBED_MODE_LIST = 1;
    const EMBED_MODE_RELAY = 2;

    const PAGING_MODE_NONE = 0;
    const PAGING_MODE_OFFSET = 1;

    protected $_embedMode = Schema::EMBED_MODE_NONE;
    protected $_pagingMode = Schema::PAGING_MODE_NONE;

    protected $_enumTypes = [];
    protected $_objectTypes = [];
    protected $_objectTypesByName = [];
    protected $_objectTypeGroups = [];

    protected $_inputObjectTypes = [];

    protected $_built = false;


    public function __construct($embedMode = null)
    {
        if($embedMode !== null){
            $this->_embedMode = $embedMode;
        }
    }

    public function embedMode($embedMode){

        $this->_embedMode = $embedMode;
        return $this;
    }

    public function embedList()
    {
        $this->_embedMode = Schema::EMBED_MODE_LIST;
        return $this;
    }

    public function embedRelay()
    {
        $this->_embedMode = Schema::EMBED_MODE_RELAY;
        return $this;
    }

    public function getEmbedMode()
    {
        return $this->_embedMode;
    }

    public function pagingMode($pagingMode)
    {
        $this->_pagingMode = $pagingMode;
        return $this;
    }

    public function pagingOffset()
    {
        $this->_pagingMode = Schema::PAGING_MODE_OFFSET;
        return $this;
    }

    public function getPagingMode()
    {
        return $this->_pagingMode;
    }

    public function enum(EnumType $enumType)
    {
        $this->_enumTypes[] = $enumType;
        return $this;
    }

    public function getEnumTypes()
    {
        return $this->_enumTypes;
    }

    public function object(ObjectType $objectType)
    {
        $this->_objectTypes[] = $objectType;
        return $this;
    }

    public function getObjectTypes()
    {
        return $this->_objectTypes;
    }

    /**
     * @param $name
     *
     * @throws Exception
     * @return ObjectType|null
     */
    public function findObjectType($name)
    {
        if(!$this->_built){
            throw new Exception(ErrorCodes::GENERAL_SYSTEM, 'Unable to find object type, schema is not built yet');
        }

        return array_key_exists($name, $this->_objectTypesByName) ? $this->_objectTypesByName[$name] : null;
    }

    public function inputObject(InputObjectType $objectType)
    {
        $this->_inputObjectTypes[] = $objectType;
        return $this;
    }

    public function getInputObjectTypes()
    {
        return $this->_inputObjectTypes;
    }

    public function objectGroup(ObjectTypeGroupInterface $objectTypeGroup)
    {
        $this->_objectTypeGroups[] = $objectTypeGroup;
        return $this;
    }

    public function getObjectTypeGroups(){

        return $this->_objectTypeGroups;
    }

    public function build(DiInterface $di){

        if($this->_built){
            return;
        }

        /** @var ObjectTypeGroupInterface $objectTypeGroup */
        foreach($this->_objectTypeGroups as $objectTypeGroup){

            $objectTypeGroup->build($this, $di);

            foreach($objectTypeGroup->getObjectTypes() as $objectType){
                $this->_objectTypesByName[$objectType->getName()] = $objectType;
            }
        }

        /** @var ObjectType $objectType */
        foreach($this->_objectTypes as $objectType){

            $objectType->build($this, $di);

            $this->_objectTypesByName[$objectType->getName()] = $objectType;
        }

        /** @var InputObjectType $inputObjectType */
        foreach($this->_inputObjectTypes as $objectType){
            $objectType->build($this, $di);
        }

        $this->_built = true;
    }


    public static function factory($embedMode = null)
    {
        return new Schema($embedMode);
    }
}
