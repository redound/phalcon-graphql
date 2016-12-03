<?php

namespace PhalconGraphQL\Definition\Fields;

use Phalcon\DiInterface;
use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Resolvers\AllModelResolver;
use PhalconGraphQL\Resolvers\FindModelResolver;

class ModelField extends Field
{
    protected $_embedMode = null;

    protected $_model;

    public function __construct($model=null, $name=null, $type=null, $description=null, $embedMode=null)
    {
        if($type === null){
            $type = Core::getShortClass($model);
        }

        parent::__construct($name, $type, $description);

        $this->_embedMode = $embedMode;
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

    public function build(Schema $schema, DiInterface $di)
    {
        if($this->_built){
            return;
        }

        $this->executeBeforeBuildPlugins($schema, $di);

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

        $this->executeAfterBuildPlugins($schema, $di);
    }


    /**
     * @param string $model
     * @param string $name
     * @param string $type
     * @param string $description
     *
     * @return static
     */
    public static function factory($model=null, $name=null, $type=null, $description=null)
    {
        return new ModelField($model, $name, $type, $description);
    }

    public static function listFactory($model=null, $name=null, $type=null, $description=null)
    {
        return self::factory($model, $name, $type, $description)->isList();
    }
}