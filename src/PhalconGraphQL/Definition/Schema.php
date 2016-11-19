<?php

namespace PhalconGraphQL\Definition;

use PhalconGraphQL\Definition\ObjectTypeGroups\ObjectTypeGroupInterface;

class Schema
{
    const EMBED_MODE_NONE = 0;
    const EMBED_MODE_NODE = 1;
    const EMBED_MODE_EDGES = 2;
    const EMBED_MODE_ALL = 3;

    protected static $defaultEmbedMode = Schema::EMBED_MODE_NONE;

    protected $_enumTypes = [];
    protected $_objectTypes = [];

    public static function getDefaultEmbedMode(){

        return self::$defaultEmbedMode;
    }

    public static function setDefaultEmbedMode($embedMode){

        self::$defaultEmbedMode = $embedMode;
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

    public function objects(ObjectTypeGroupInterface $objectTypeGroup)
    {
        $this->_objectTypes = array_merge($this->_objectTypes, $objectTypeGroup->getObjectTypes());
        return $this;
    }

    public function getObjectTypes()
    {
        return $this->_objectTypes;
    }

    public static function factory()
    {
        return new Schema;
    }
}
