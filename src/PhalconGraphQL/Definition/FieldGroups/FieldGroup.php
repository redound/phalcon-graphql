<?php

namespace PhalconGraphQL\Definition\FieldGroups;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Schema;

class FieldGroup implements FieldGroupInterface
{
    protected $_fields = [];
    protected $_handler;
    protected $_allowedRoles = [];
    protected $_deniedRoles = [];
    protected $_allowedFieldRoles = [];
    protected $_deniedFieldRoles = [];
    protected $_plugins = [];

    protected $_build = false;

    public function __construct($handler=null)
    {
        if($handler === null){
            $handler = $this->getDefaultHandler();
        }

        $this->_handler = $handler;
    }

    public function add(Field $field){

        $this->_fields[] = $field;
        return $this;
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

    public function plugin($plugin)
    {
        $this->_plugins[] = $plugin;
        return $this;
    }


    public function build(Schema $schema, DiInterface $di)
    {
        $fields = array_merge($this->_fields, $this->getDefaultFields($schema, $di));

        /** @var Field $field */
        foreach($fields as $field){

            if(!$field->getHandler()) {
                $field->handler($this->_handler);
            }

            $field->allow($this->_allowedRoles);
            $field->deny($this->_deniedRoles);

            $fieldName = $field->getName();

            if(array_key_exists($fieldName, $this->_allowedFieldRoles)){
                $field->allow($this->_allowedFieldRoles[$fieldName]);
            }

            if(array_key_exists($fieldName, $this->_deniedFieldRoles)){
                $field->deny($this->_deniedFieldRoles[$fieldName]);
            }

            foreach($this->_plugins as $plugin){
                $field->plugin($plugin);
            }
        }

        $this->_fields = $fields;
        $this->_build = true;
    }

    protected function getDefaultFields(Schema $schema, DiInterface $di){

        // Override
        return [];
    }

    protected function getDefaultHandler(){

        return null;
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * @return static
     */
    public static function factory($handler=null){

        return new FieldGroup($handler);
    }
}