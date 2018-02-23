<?php

namespace PhalconGraphQL\Definition;

use Phalcon\DiInterface;
use PhalconGraphQL\Exception;
use PhalconGraphQL\Definition\FieldGroups\FieldGroupInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Plugins\ObjectTypePluginInterface;
use PhalconGraphQL\Plugins\Plugin;

class UnionType
{
    protected $_name;
    protected $_description;
    protected $_handler;
    protected $_types = [];
    protected $_allowedRoles = [];
    protected $_deniedRoles = [];
    protected $_plugins = [];
    protected $_built = false;

    public function __construct($name=null, $description=null)
    {
        if($name !== null){
            $this->_name = $name;
        }

        if($description !== null){
            $this->_description = $description;
        }
    }

    public function plugin(ObjectTypePluginInterface $plugin)
    {
        $this->_plugins[] = $plugin;

        return $this;
    }

    public function getPlugins()
    {
        return $this->_plugins;
    }

    /**
     * @param string $name Name for the ObjectType
     *
     * @return static
     */
    public function name($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $description Description for the ObjectType
     *
     * @return static
     */
    public function description($description)
    {
        $this->_description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param string $handler Handler for the ObjectType
     *
     * @return static
     */
    public function handler($handler)
    {
        $this->_handler = $handler;
        return $this;
    }

    public function getHandler()
    {
        return $this->_handler;
    }

    /**
     * @param $typeName String Add type to UnionType
     *
     * @return static
     */
    public function type($typeName)
    {
        // Remove type if already exists
        $this->removeType($typeName);

        $this->_types[] = $typeName;
        $this->_built = false;

        return $this;
    }

    public function removeType($typeName)
    {
        $foundIndex = array_search($typeName, $this->_types);

        if($foundIndex !== false) {
            array_splice($this->_types, $foundIndex, 1);
        }

        return $this;
    }

    public function getTypes()
    {
        if(!$this->_built){
            throw new Exception("Unable to get types from '" . $this->getName() . "', object type is not built yet'");
        }

        return $this->_types;
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

    public function build(Schema $schema, DiInterface $di){

        if($this->_built){
            return;
        }

        /** @var Plugin $plugin */
        foreach($this->_plugins as $plugin){
            $plugin->setSchema($schema);
        }

        $this->executeBeforeBuildPlugins($schema, $di);
        $this->_built = true;
        $this->executeAfterBuildPlugins($schema, $di);
    }

    protected function executeBeforeBuildPlugins(Schema $schema, DiInterface $di)
    {
        /** @var Plugin $plugin */
        foreach(array_merge($schema->getPlugins(), $this->_plugins) as $plugin){
            $plugin->beforeBuildUnionType($this, $di);
        }
    }

    protected function executeAfterBuildPlugins(Schema $schema, DiInterface $di)
    {
        /** @var Plugin $plugin */
        foreach(array_merge($schema->getPlugins(), $this->_plugins) as $plugin){
            $plugin->afterBuildUnionType($this, $di);
        }
    }

    /**
     * @return static
     */
    public static function factory($name=null, $description=null)
    {
        return new UnionType($name, $description);
    }

    public static function query($description=null)
    {
        return self::factory(Types::QUERY, $description);
    }

    public static function mutation($description=null)
    {
        return self::factory(Types::MUTATION, $description);
    }

    public static function viewer($description=null)
    {
        return self::factory(Types::VIEWER, $description);
    }
}
