<?php

namespace PhalconGraphQL\Definition\ObjectTypeGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Handlers\ListEmbedHandler;
use PhalconGraphQL\Handlers\PassHandler;

class EmbeddedObjectTypeGroup extends ObjectTypeGroup
{
    /** @var ObjectType */
    protected $_mainObjectType;

    protected $_embedMode;

    protected $_collectionFields = [];

    protected $_itemFields = [];


    public function __construct(ObjectType $mainObjectType)
    {
        $this->_mainObjectType = $mainObjectType;
    }

    public function embedList(){

        $this->_embedMode = Schema::EMBED_MODE_LIST;
        return $this;
    }

    public function embedRelay(){

        $this->_embedMode = Schema::EMBED_MODE_RELAY;
        return $this;
    }

    public function collectionField(Field $field){

        $this->_collectionFields[] = $field;
        return $this;
    }

    public function getCollectionFields()
    {
        return $this->_collectionFields;
    }

    public function itemField(Field $field){

        $this->_itemFields[] = $field;
        return $this;
    }

    public function getItemFields()
    {
        return $this->_itemFields;
    }

    public function getDefaultObjectTypes(Schema $schema, DiInterface $di){

        $embedMode = $this->_embedMode;

        if($embedMode === null){
            $embedMode = $schema->getEmbedMode();
        }

        $objectTypes = [];

        $name = $this->_mainObjectType->getName();

        // Object
        $objectTypes[] = $this->_mainObjectType;

        if($embedMode == Schema::EMBED_MODE_RELAY) {

            $connectionName = Types::addConnection($name);
            $edgeName = Types::addEdge($name);

            // Edges
            $connectionType = ObjectType::factory($connectionName)
                ->handler(PassHandler::class)
                ->field(Field::listFactory('edges', $edgeName)
                    ->nonNull()
                    ->isNonNullList(true)
                )
                ->field(Field::int('totalCount'));

            foreach ($this->_collectionFields as $field) {
                $connectionType->field($field);
            }

            $objectTypes[] = $connectionType;

            // Node
            $edgeType = ObjectType::factory($edgeName)
                ->handler(PassHandler::class)
                ->field(Field::factory('node', $name)
                    ->nonNull()
                );

            foreach ($this->_itemFields as $field) {
                $edgeType->field($field);
            }

            $objectTypes[] = $edgeType;
        }
        else if($embedMode == Schema::EMBED_MODE_LIST) {

            $listName = Types::addList($name);

            // List
            $listType = ObjectType::factory($listName)
                ->handler(ListEmbedHandler::class)
                ->field(Field::listFactory('items', $name)
                    ->nonNull()
                )
                ->field(Field::int('itemCount'))
                ->field(Field::int('totalCount'))
                ->field(Field::int('offset'))
                ->field(Field::int('limit'));

            foreach ($this->_collectionFields as $field) {
                $listType->field($field);
            }

            $objectTypes[] = $listType;
        }

        return $objectTypes;
    }

    public static function factory($mainObjectType=null){

        return new static($mainObjectType);
    }
}