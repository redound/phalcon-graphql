<?php

namespace PhalconGraphQL\Definition\Fields;

use Phalcon\DiInterface;
use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Plugins\Plugin;
use PhalconGraphQL\Resolvers\AllModelResolver;
use PhalconGraphQL\Resolvers\FindModelResolver;

class ModelField extends Field
{
    protected $_embedMode = null;

    protected $_model;

    public function __construct($model=null, $name=null, $type=null)
    {
        if($type === null){
            $type = Core::getShortClass($model);
        }

        parent::__construct($name, $type);

        $this->_model = $model;
    }

    /**
     * @param string $modelClass
     * @return static
     */
    public function model($modelClass)
    {
        $this->_model = $modelClass;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->_model;
    }

    public function getEmbedMode()
    {
        return $this->_embedMode;
    }

    public function embedMode($embedMode)
    {
        $this->_embedMode = $embedMode;
        return $this;
    }

    public function embedRelay(){

        $this->_embedMode = Schema::EMBED_MODE_RELAY;
        return $this;
    }

    public function noEmbed(){

        $this->_embedMode = Schema::EMBED_MODE_NONE;
        return $this;
    }

    public function type($type)
    {
        $this->_type = $type;
        return $this;
    }

    public function isList($isList = true)
    {
        $this->_isList = $isList;
        return $this;
    }

    public function build(Schema $schema, ObjectType $objectType, DiInterface $di)
    {
        if($this->_built){
            return;
        }

        /** @var Plugin $plugin */
        foreach($this->_plugins as $plugin){
            $plugin->setSchema($schema);
        }

        $this->executeBeforeBuildPlugins($schema, $objectType, $di);

        if($this->_isList) {

            $embedMode = $this->_embedMode;

            if($embedMode === null){
                $embedMode = $schema->getEmbedMode();
            }

            if($embedMode == Schema::EMBED_MODE_LIST){

                $this->_type = Types::addList($this->_type);
                $this->_isList = false;
            }
            if($embedMode == Schema::EMBED_MODE_RELAY){

                $this->_type = Types::addConnection($this->_type);
                $this->_isList = false;
            }
        }

        $this->_built = true;

        $this->executeAfterBuildPlugins($schema, $objectType, $di);
    }


    /**
     * @param string $model
     * @param string $name
     *
     * @return static
     */
    public static function factory($model=null, $name=null)
    {
        return new static($model, $name);
    }

    public static function listFactory($model=null, $name=null)
    {
        return self::factory($model, $name)->isList();
    }
}