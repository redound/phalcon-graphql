<?php

namespace PhalconGraphQL\Definition\ObjectTypeGroups;

use PhalconGraphQL\Definition\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Handlers\PassHandler;

class EmbeddedObjectTypeGroup implements ObjectTypeGroupInterface
{
    /** @var ObjectType */
    protected $_mainObjectType;

    protected $_embedMode;

    protected $_objectTypes;

    protected $_built;


    public function __construct(ObjectType $mainObjectType, $embedMode = null)
    {
        $this->_mainObjectType = $mainObjectType;
        $this->_embedMode = $embedMode === null ? Schema::getDefaultEmbedMode() : $embedMode;
    }

    public function onlyNode(){

        $this->_embedMode = Schema::EMBED_MODE_NODE;
        return $this;
    }

    public function onlyEdges(){

        $this->_embedMode = Schema::EMBED_MODE_EDGES;
        return $this;
    }

    public function all(){

        $this->_embedMode = Schema::EMBED_MODE_ALL;
        return $this;
    }

    protected function build(){

        $objectTypes = [];

        $name = $this->_mainObjectType->getName();

        $connectionName = Types::connection($name);
        $edgeName = Types::edge($name);

        $embedNode = in_array($this->_embedMode, [Schema::EMBED_MODE_ALL, Schema::EMBED_MODE_NODE]);
        $embedEdges = in_array($this->_embedMode, [Schema::EMBED_MODE_ALL, Schema::EMBED_MODE_EDGES]);

        // Object
        $objectTypes[] = $this->_mainObjectType;

        // Edges
        if($embedEdges) {

            $connectionType = ObjectType::factory($connectionName)
                ->handler(PassHandler::class)
                ->field(Field::listFactory('edges', $embedNode ? $edgeName : $name)
                    ->nonNull()
                    ->isNonNullList($embedNode)
                );

//        foreach($connectionFields as $field){
//            $connectionType->field($field);
//        }

            $objectTypes[] = $connectionType;
        }

        // Node
        if($embedNode) {

            $edgeType = ObjectType::factory($edgeName)
                ->handler(PassHandler::class)
                ->field(Field::factory('node', $name)
                    ->nonNull()
                );

//        foreach($edgeFields as $field){
//            $edgeType->field($field);
//        }

            $objectTypes[] = $edgeType;
        }

        $this->_objectTypes = $objectTypes;
    }

    /**
     * @return ObjectType[]
     */
    public function getObjectTypes()
    {
        if(!$this->_built){

            $this->build();
            $this->_built = true;
        }

        return $this->_objectTypes;
    }

    /**
     * @param ObjectType $mainObjectType
     * @param int $embedMode
     *
     * @return static
     */
    public static function factory(ObjectType $mainObjectType, $embedMode = null){

        return new EmbeddedObjectTypeGroup($mainObjectType, $embedMode);
    }
}