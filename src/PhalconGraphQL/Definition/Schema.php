<?php

namespace PhalconGraphQL\Definition;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\ObjectTypeGroups\ObjectTypeGroupInterface;

class Schema
{
    const EMBED_MODE_NONE = 0;
    const EMBED_MODE_NODE = 1;
    const EMBED_MODE_EDGES = 2;
    const EMBED_MODE_ALL = 3;

    protected $_embedMode = Schema::EMBED_MODE_NONE;

    protected $_enumTypes = [];
    protected $_objectTypes = [];
    protected $_objectTypeGroups = [];

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

    public function embedOnlyEdges()
    {
        $this->_embedMode = Schema::EMBED_MODE_EDGES;
        return $this;
    }

    public function embedOnlyNode()
    {
        $this->_embedMode = Schema::EMBED_MODE_NODE;
        return $this;
    }

    public function embed()
    {
        $this->_embedMode = Schema::EMBED_MODE_ALL;
        return $this;
    }

    public function getEmbedMode()
    {
        return $this->_embedMode;
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

        $objectTypes = $this->_objectTypes;

        /** @var ObjectTypeGroupInterface $objectTypeGroup */
        foreach($this->_objectTypeGroups as $objectTypeGroup){

            $objectTypeGroup->build($this, $di);
            $objectTypes = array_merge($objectTypes, $objectTypeGroup->getObjectTypes());
        }

        /** @var ObjectType $objectType */
        foreach($objectTypes as $objectType){
            $objectType->build($this, $di);
        }

        $this->_built = true;
    }


    public static function factory($embedMode = null)
    {
        return new Schema($embedMode);
    }
}
