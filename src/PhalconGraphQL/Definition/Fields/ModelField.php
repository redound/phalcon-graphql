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

    public function embed(){

        $this->_embedMode = Schema::EMBED_MODE_ALL;
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

        if($this->_isList) {

            $embedMode = $this->_embedMode;

            if($embedMode === null){
                $embedMode = $schema->getEmbedMode();
            }

            $embedNode = in_array($embedMode, [Schema::EMBED_MODE_ALL, Schema::EMBED_MODE_NODE]);
            $embedEdges = in_array($embedMode, [Schema::EMBED_MODE_ALL, Schema::EMBED_MODE_EDGES]);

            if($embedEdges){

                $this->_type = Types::connection($this->_type);
                $this->_isList = false;
            }
            else if($embedNode){

                $this->_type = Types::edge($this->_type);
                $this->_isList = true;
            }
        }

        $this->_built = true;
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