<?php

namespace PhalconGraphQL\Definition;

use Phalcon\DiInterface;
use PhalconGraphQL\Exception;
use PhalconGraphQL\Definition\FieldGroups\FieldGroupInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Plugins\ObjectTypePluginInterface;
use PhalconGraphQL\Plugins\Plugin;

class ObjectType
{
    protected $_name;
    protected $_description;
    protected $_handler;
    protected $_fields = [];
    protected $_fieldByName = [];
    protected $_fieldGroups = [];
    protected $_allowedRoles = [];
    protected $_deniedRoles = [];
    protected $_allowedFieldRoles = [];
    protected $_deniedFieldRoles = [];
    protected $_plugins = [];
    protected $_fieldPlugins = [];
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

    public function plugin(ObjectTypePluginInterface $plugin, $addToFields=true)
    {
        $this->_plugins[] = $plugin;

        if($addToFields){
            $this->_fieldPlugins[] = $plugin;
        }

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
     * @param Field $field Add field to ObjectType
     *
     * @return static
     */
    public function field(Field $field)
    {
        // Remove field if already exists
        $this->removeField($field->getName());

        $this->_fields[] = $field;
        $this->_fieldByName[$field->getName()] = $field;

        $this->_built = false;

        return $this;
    }

    public function removeField($fieldName)
    {
        $field = $this->findField($fieldName);

        if($field){

            $fieldIndex = array_search($field, $this->_fields);
            if($fieldIndex !== false){

                array_splice($this->_fields, $foundIndex, 1);
                unset($this->_fieldByName[$fieldName]);
            }
        }

        return $this;
    }

    public function fieldExists($fieldName)
    {
        return array_key_exists($fieldName, $this->_fieldByName);
    }

    public function findField($fieldName)
    {
        return array_key_exists($fieldName, $this->_fieldByName) ? $this->_fieldByName[$fieldName] : null;
    }

    public function getFields()
    {
        if(!$this->_built){
            throw new Exception("Unable to get fields from '" . $this->getName() . "', object type is not built yet'");
        }

        return $this->_fields;
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

    public function allowField($fieldName, $roles)
    {
        $fieldRoles = array_key_exists($fieldName, $this->_allowedFieldRoles) ? $this->_allowedFieldRoles[$fieldName] : [];
        $fieldRoles = array_merge($fieldRoles, is_array($roles) ? $roles : [$roles]);
        $this->_allowedFieldRoles[$fieldName] = $fieldRoles;

        return $this;
    }

    public function denyField($fieldName, $roles)
    {
        $fieldRoles = array_key_exists($fieldName, $this->_deniedFieldRoles) ? $this->_deniedFieldRoles[$fieldName] : [];
        $fieldRoles = array_merge($fieldRoles, is_array($roles) ? $roles : [$roles]);
        $this->_deniedFieldRoles[$fieldName] = $fieldRoles;

        return $this;
    }

    public function fieldGroup(FieldGroupInterface $fieldGroup)
    {
        $this->_fieldGroups[] = $fieldGroup;
        return $this;
    }

    /**
     * @return array
     */
    public function getFieldGroups()
    {
        return $this->_fieldGroups;
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

        /** @var Field $field */
        foreach($this->_fields as $field){

            $field->allow($this->_allowedRoles);
            $field->deny($this->_deniedRoles);

            $fieldName = $field->getName();

            if(array_key_exists($fieldName, $this->_allowedFieldRoles)){
                $field->allow($this->_allowedFieldRoles[$fieldName]);
            }

            if(array_key_exists($fieldName, $this->_deniedFieldRoles)){
                $field->deny($this->_deniedFieldRoles[$fieldName]);
            }

            foreach($this->_fieldPlugins as $plugin){
                $field->plugin($plugin);
            }
        }

        /** @var FieldGroupInterface $group */
        foreach($this->_fieldGroups as $group){

            $group->allow($this->_allowedRoles);
            $group->deny($this->_deniedRoles);

            foreach($this->_allowedFieldRoles as $fieldName => $roles){
                $group->allowField($fieldName, $roles);
            }

            foreach($this->_deniedFieldRoles as $fieldName => $roles) {
                $group->denyField($fieldName, $roles);
            }

            foreach($this->_fieldPlugins as $plugin){
                $group->plugin($plugin);
            }
        }

        $this->_built = true;

        $this->executeAfterBuildPlugins($schema, $di);
    }

    protected function executeBeforeBuildPlugins(Schema $schema, DiInterface $di)
    {
        /** @var Plugin $plugin */
        foreach(array_merge($schema->getPlugins(), $this->_plugins) as $plugin){
            $plugin->beforeBuildObjectType($this, $di);
        }
    }

    protected function executeAfterBuildPlugins(Schema $schema, DiInterface $di)
    {
        /** @var Plugin $plugin */
        foreach(array_merge($schema->getPlugins(), $this->_plugins) as $plugin){
            $plugin->afterBuildObjectType($this, $di);
        }
    }

    /**
     * @return static
     */
    public static function factory($name=null, $description=null)
    {
        return new ObjectType($name, $description);
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
