<?php

namespace PhalconGraphQL\Definition\Fields;

use Phalcon\Di\DiInterface;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\Types;
use PhalconGraphQL\Plugins\FieldPluginInterface;
use PhalconGraphQL\Plugins\Plugin;
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
    protected $_allowedRoles = [];
    protected $_deniedRoles = [];
    protected $_plugins = [];
    protected $_built = false;

    public function __construct($name=null, $type=null)
    {
        if($name !== null){
            $this->_name = $name;
        }

        if($type !== null){
            $this->_type = $type;
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

    public function allow($roles)
    {
        $this->_allowedRoles = array_merge($this->_allowedRoles, is_array($roles) ? $roles : [$roles]);
        return $this;
    }

    public function deny($roles)
    {
        $this->_deniedRoles = array_merge($this->_deniedRoles, is_array($roles) ? $roles : [$roles]);
        return $this;
    }

    public function getAllowedRoles()
    {
        return $this->_allowedRoles;
    }

    public function getDeniedRoles()
    {
        return $this->_deniedRoles;
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



    public static function factory($name=null, $type=null)
    {
        return new static($name, $type);
    }

    public static function listFactory($name=null, $type=null)
    {
        return self::factory($name, $type)->isList();
    }


    public static function string($name=null)
    {
        return self::factory($name, Types::STRING);
    }

    public static function int($name=null)
    {
        return self::factory($name, Types::INT);
    }

    public static function float($name=null)
    {
        return self::factory($name, Types::FLOAT);
    }

    public static function boolean($name=null)
    {
        return self::factory($name, Types::BOOLEAN);
    }

    public static function id($name=null)
    {
        return self::factory($name, Types::ID);
    }


    public static function viewer()
    {
        return self::factory('viewer', Types::VIEWER)
            ->nonNull()
            ->resolver(EmptyResolver::class);
    }
}
