<?php

namespace PhalconGraphQL\Definition\Collections;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\EnumType;
use PhalconGraphQL\Definition\FieldGroups\FieldGroupInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\InputObjectType;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\ObjectTypeGroups\ObjectTypeGroupInterface;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\SchemaMountableInterface;

class Collection implements SchemaMountableInterface
{
    protected $_enumTypes = [];
    protected $_objectTypes = [];
    protected $_objectTypeGroups = [];
    protected $_inputObjectTypes = [];
    protected $_allowedRoles = [];
    protected $_deniedRoles = [];
    protected $_allowedFieldRoles = [];
    protected $_deniedFieldRoles = [];
    protected $_fields = [];
    protected $_fieldGroups = [];


    public function __construct()
    {
        $this->initialize();
    }

    public function enum(EnumType $enumType)
    {
        $this->_enumTypes[] = $enumType;
        return $this;
    }

    public function getEnumTypes()
    {
        return $this->_enumTypes;
    }

    public function object(ObjectType $objectType)
    {
        $this->_objectTypes[] = $objectType;
        return $this;
    }

    public function getObjectTypes()
    {
        return $this->_objectTypes;
    }

    public function inputObject(InputObjectType $objectType)
    {
        $this->_inputObjectTypes[] = $objectType;
        return $this;
    }

    public function getInputObjectTypes()
    {
        return $this->_inputObjectTypes;
    }

    public function objectGroup(ObjectTypeGroupInterface $objectTypeGroup)
    {
        $this->_objectTypeGroups[] = $objectTypeGroup;
        return $this;
    }

    public function getObjectTypeGroups(){

        return $this->_objectTypeGroups;
    }

    public function field($objectTypeName, Field $field)
    {
        $typeFields = array_key_exists($objectTypeName, $this->_fields) ? $this->_fields[$objectTypeName] : [];
        $typeFields[] = $field;

        $this->_fields[$objectTypeName] = $typeFields;

        return $this;
    }

    public function getFields()
    {
        return $this->_fields;
    }

    public function fieldGroup($objectTypeName, FieldGroupInterface $fieldGroup)
    {
        $typeFieldGroups = array_key_exists($objectTypeName, $this->_fieldGroups) ? $this->_fieldGroups[$objectTypeName] : [];
        $typeFieldGroups[] = $fieldGroup;

        $this->_fieldGroups[$objectTypeName] = $typeFieldGroups;

        return $this;
    }

    public function getFieldGroups()
    {
        return $this->_fieldGroups;
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


    protected function initialize()
    {

    }

    public function build(Schema $schema, DiInterface $di)
    {
        foreach($this->_fields as $objectTypeFields){

            /** @var Field $field */
            foreach($objectTypeFields as $field) {

                $field->allow($this->_allowedRoles);
                $field->deny($this->_deniedRoles);

                $fieldName = $field->getName();

                if (array_key_exists($fieldName, $this->_allowedFieldRoles)) {
                    $field->allow($this->_allowedFieldRoles[$fieldName]);
                }

                if (array_key_exists($fieldName, $this->_deniedFieldRoles)) {
                    $field->deny($this->_deniedFieldRoles[$fieldName]);
                }
            }
        }

        foreach($this->_fieldGroups as $objectTypeGroups){

            /** @var Field $field */
            foreach($objectTypeGroups as $field) {

                $group->allow($this->_allowedRoles);
                $group->deny($this->_deniedRoles);

                foreach ($this->_allowedFieldRoles as $fieldName => $roles) {
                    $group->allowField($fieldName, $roles);
                }

                foreach ($this->_deniedFieldRoles as $fieldName => $roles) {
                    $group->denyField($fieldName, $roles);
                }
            }
        }
    }

    public static function factory()
    {
        return new Collection();
    }
}