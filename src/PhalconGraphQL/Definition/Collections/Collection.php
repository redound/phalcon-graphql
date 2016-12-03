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

    protected function initialize()
    {

    }

    public function build(Schema $schema, DiInterface $di)
    {
        //
    }

    public static function factory()
    {
        return new Collection();
    }
}