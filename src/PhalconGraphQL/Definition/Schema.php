<?php

namespace PhalconGraphQL\Definition;

use PhalconGraphQL\Handlers\PassHandler;

class Schema
{
    protected $_enumTypes = [];
    protected $_objectTypes = [];

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

    public function embeddedObject(ObjectType $objectType, array $connectionFields=[], array $edgeFields=[])
    {
        $name = $objectType->getName();

        $connectionName = Types::connection($name);
        $edgeName = Types::edge($name);

        // Object
        $this->object($objectType);

        // Connection
        $connectionType = ObjectType::factory($connectionName)
            ->handler(PassHandler::class)
            ->field(Field::listFactory('edges', $edgeName)
                ->nonNull()
                ->isNonNullList()
            );

        foreach($connectionFields as $field){
            $connectionType->field($field);
        }

        $this->object($connectionType);

        // Edge
        $edgeType = ObjectType::factory($edgeName)
            ->handler(PassHandler::class)
            ->field(Field::factory('node', $name)
                ->nonNull()
            );

        foreach($edgeFields as $field){
            $edgeType->field($field);
        }

        $this->object($edgeType);

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
