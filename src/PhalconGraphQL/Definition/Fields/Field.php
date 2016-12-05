<?php

namespace PhalconGraphQL\Definition\Fields;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Plugins\FieldPluginInterface;
use PhalconGraphQL\Resolvers\EmptyResolver;
use PhalconGraphQL\Resolvers\Resolver;

class Field
{
    protected $_name;
    protected $_description;
    protected $_type;
    protected $_nonNull = false;
    protected $_isList = false;
    protected $_isNonNullList;
    protected $_resolver = Resolver::class;
    protected $_handler;
    protected $_args = [];
    protected $_plugins = [];
    protected $_built = false;

    public function __construct($name=null, $type=null, $description=null)
    {
        if($name !== null){
            $this->_name = $name;
        }

        if($type !== null){
            $this->_type = $type;
        }

        if($description !== null){
            $this->_description = $description;
        }
    }

    public function plugin(FieldPluginInterface $plugin)
    {
        $this->_plugins[] = $plugin;
        return $this;
    }

    public function getPlugins()
    {
        return $this->_plugins;
    }

    public function name($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function description($description)
    {
        $this->_description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function type($type)
    {
        $this->_type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function nonNull($nonNull = true)
    {
        $this->_nonNull = $nonNull;
        return $this;
    }

    public function null($null = true)
    {
        $this->_nonNull = !$null;
        return $this;
    }

    public function getNonNull()
    {
        return $this->_nonNull;
    }

    public function isList($isList = true)
    {
        $this->_isList = $isList;
        return $this;
    }

    public function getIsList()
    {
        return $this->_isList;
    }

    public function isNonNullList($isNonNullList = true)
    {
        $this->_isNonNullList = $isNonNullList;
        return $this;
    }

    public function getIsNonNullList()
    {
        return $this->_isNonNullList;
    }

    public function resolver($resolver)
    {
        $this->_resolver = $resolver;
        return $this;
    }

    public function getResolver()
    {
        return $this->_resolver;
    }

    public function handler($handler)
    {
        $this->_handler = $handler;
        return $this;
    }

    public function getHandler()
    {
        return $this->_handler;
    }

    public function arg(InputField $inputField)
    {
        $this->_args[] = $inputField;
        return $this;
    }

    public function getArgs()
    {
        return $this->_args;
    }

    public function build(Schema $schema, ObjectType $objectType, DiInterface $di)
    {
        if($this->_built){
            return;
        }

        $this->executeBeforeBuildPlugins($schema, $objectType, $di);
        $this->_built = true;
        $this->executeAfterBuildPlugins($schema, $objectType, $di);
    }

    protected function executeBeforeBuildPlugins(Schema $schema, ObjectType $objectType, DiInterface $di)
    {
        /** @var FieldPluginInterface $plugin */
        foreach(array_merge($schema->getPlugins(), $this->_plugins) as $plugin){
            $plugin->beforeBuildField($this, $objectType, $di);
        }
    }

    protected function executeAfterBuildPlugins(Schema $schema, ObjectType $objectType, DiInterface $di)
    {
        /** @var FieldPluginInterface $plugin */
        foreach(array_merge($schema->getPlugins(), $this->_plugins) as $plugin){
            $plugin->afterBuildField($this, $objectType, $di);
        }
    }



    public static function factory($name=null, $type=null, $description=null)
    {
        return new Field($name, $type, $description);
    }

    public static function listFactory($name=null, $type=null, $description=null)
    {
        return self::factory($name, $type, $description)->isList();
    }


    public static function string($name=null, $description=null)
    {
        return self::factory($name, Types::STRING, $description);
    }

    public static function int($name=null, $description=null)
    {
        return self::factory($name, Types::INT, $description);
    }

    public static function float($name=null, $description=null)
    {
        return self::factory($name, Types::FLOAT, $description);
    }

    public static function boolean($name=null, $description=null)
    {
        return self::factory($name, Types::BOOLEAN, $description);
    }

    public static function id($name=null, $description=null)
    {
        return self::factory($name, Types::ID, $description);
    }


    public static function viewer()
    {
        return self::factory('viewer', Types::VIEWER)
            ->nonNull()
            ->resolver(EmptyResolver::class);
    }
}
