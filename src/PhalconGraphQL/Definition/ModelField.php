<?php

namespace PhalconGraphQL\Definition;

use PhalconGraphQL\Core;
use PhalconGraphQL\Resolvers\AllModelResolver;
use PhalconGraphQL\Resolvers\FindModelResolver;

class ModelField extends Field
{
    protected $_originalType;
    protected $_originalIsList = false;
    protected $_embedMode;

    protected $_model;

    public function __construct($model=null, $name=null, $type=null, $description=null, $embedMode=null)
    {
        if($type === null){
            $type = Core::getShortClass($model);
        }

        parent::__construct($name, $type, $description);

        $this->_originalType = $type;

        if($embedMode === null){
            $embedMode = Schema::getDefaultEmbedMode();
        }

        $this->_embedMode = $embedMode;
        $this->updateType();

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
        $this->updateType();

        return $this;
    }

    public function embed(){

        $this->_embedMode = Schema::EMBED_MODE_ALL;
        $this->updateType();

        return $this;
    }

    public function noEmbed(){

        $this->_embedMode = Schema::EMBED_MODE_NONE;
        $this->updateType();

        return $this;
    }

    public function type($type)
    {
        $this->_type = $type;
        $this->_originalType = $type;

        $this->updateType();

        return $this;
    }

    public function isList($isList = true)
    {
        $this->_isList = $isList;
        $this->_originalIsList = $isList;

        $this->updateType();

        return $this;
    }

    protected function updateType(){

        $embedNode = in_array($this->_embedMode, [Schema::EMBED_MODE_ALL, Schema::EMBED_MODE_NODE]);
        $embedEdges = in_array($this->_embedMode, [Schema::EMBED_MODE_ALL, Schema::EMBED_MODE_EDGES]);

        if($embedEdges && $this->_originalIsList){

            $this->_type = Types::connection($this->_originalType);
            $this->_isList = false;
        }
        else if($embedNode && !$this->_originalIsList){

            $this->_type = Types::edge($this->_originalType);
            $this->_isList = false;
        }
        else if($embedNode && $this->_originalIsList){

            $this->_type = Types::edge($this->_originalType);
            $this->_isList = true;
        }
        else {

            $this->_type = $this->_originalType;
            $this->_isList = $this->_originalIsList;
        }
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


    public static function all($model=null, $name=null, $type=null, $description=null)
    {
        if($name === null){
            $name = 'all' . ucfirst(Core::getShortClass($model)) . 's';
        }

        return self::factory($model, $name, $type, $description)
            ->resolver(AllModelResolver::class)
            ->isList()
            ->nonNull();
    }

    public static function find($model=null, $name=null, $type=null, $description=null)
    {
        if($name === null){
            $name = 'find' . ucfirst(Core::getShortClass($model));
        }

        return self::factory($model, $name, $type, $description)
            ->resolver(FindModelResolver::class)
            ->arg(InputField::factory('id', Types::ID));
    }
}