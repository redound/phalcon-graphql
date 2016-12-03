<?php

namespace PhalconGraphQL\Definition;

use Phalcon\DiInterface;
use PhalconApi\Exception;
use PhalconGraphQL\Definition\FieldGroups\FieldGroupInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Plugins\ObjectTypePluginInterface;

class ObjectType
{
    protected $_name;
    protected $_description;
    protected $_handler;
    protected $_fields = [];
    protected $_fieldGroups = [];
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
     * @param Field $field Add field to ObjectType
     *
     * @return static
     */
    public function field(Field $field)
    {
        // Remove field if already exists
        $this->removeField($field->getName());

        $this->_fields[] = $field;
        $this->_built = false;

        return $this;
    }

    public function removeField($fieldName)
    {
        $foundIndex = null;

        foreach($this->_fields as $index => $field){

            if($field->getName() == $fieldName){

                $foundIndex = $index;
                break;
            }
        }

        if($foundIndex !== null) {
            array_splice($this->_fields, $foundIndex, 1);
        }

        return $this;
    }

    public function fieldExists($fieldName)
    {
        foreach($this->_fields as $index => $field){

            if($field->getName() == $fieldName){
                return true;
            }
        }

        return false;
    }

    public function getFields()
    {
        if(!$this->_built){
            throw new Exception("Unable to get fields from '" . $this->getName() . "', object type is not built yet'");
        }

        return $this->_fields;
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

        $this->executeBeforeBuildPlugins($schema, $di);

        /** @var FieldGroupInterface $fieldGroup */
        foreach($this->_fieldGroups as $fieldGroup){

            $fieldGroup->build($schema, $di);

            foreach($fieldGroup->getFields() as $field){
                $this->field($field);
            }
        }

        /** @var Field $field */
        foreach($this->_fields as $field){
            $field->build($schema, $di);
        }

        $this->_built = true;

        $this->executeAfterBuildPlugins($schema, $di);
    }

    protected function executeBeforeBuildPlugins(Schema $schema, DiInterface $di)
    {
        /** @var ObjectTypePluginInterface $plugin */
        foreach(array_merge($schema->getPlugins(), $this->_plugins) as $plugin){
            $plugin->beforeBuildObjectType($this, $di);
        }
    }

    protected function executeAfterBuildPlugins(Schema $schema, DiInterface $di)
    {
        /** @var ObjectTypePluginInterface $plugin */
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
