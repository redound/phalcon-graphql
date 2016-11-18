<?php

namespace PhalconGraphQL\Definition;

use PhalconGraphQL\Core;
use PhalconGraphQL\Resolvers\AllModelResolver;
use PhalconGraphQL\Resolvers\FindModelResolver;

class ModelField extends Field
{
    protected $_model;

    public function __construct($model=null, $name=null, $type=null, $description=null)
    {
        if($type === null){
            $type = Core::getShortClass($model);
        }

        parent::__construct($name, $type, $description);

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