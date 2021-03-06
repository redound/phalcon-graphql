<?php

namespace PhalconGraphQL\Definition;

use GraphQL\Type\Definition\ScalarType;
use Phalcon\Acl;
use Phalcon\Di\DiInterface;
use PhalconGraphQL\Definition\FieldGroups\FieldGroupInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectTypeGroups\ObjectTypeGroupInterface;
use PhalconGraphQL\Definition\ScalarTypes\AnyScalarType;
use PhalconGraphQL\Definition\ScalarTypes\DateScalarType;
use PhalconGraphQL\Definition\ScalarTypes\DateTimeScalarType;
use PhalconGraphQL\Definition\ScalarTypes\JsonScalarType;
use PhalconGraphQL\Definition\ScalarTypes\TimeScalarType;
use PhalconGraphQL\Plugins\PluginInterface;

class Schema implements \PhalconApi\Acl\MountableInterface
{
    const EMBED_MODE_NONE = 0;
    const EMBED_MODE_LIST = 1;
    const EMBED_MODE_RELAY = 2;

    protected $_embedMode = Schema::EMBED_MODE_NONE;

    protected $_scalarTypes = [];

    protected $_enumTypes = [];
    protected $_enumTypesByName = [];

    protected $_objectTypes = [];
    protected $_objectTypesByName = [];
    protected $_objectTypeGroups = [];

    protected $_unionTypes = [];
    protected $_unionTypesByName = [];

    protected $_inputObjectTypes = [];

    protected $_typesByName = [];

    protected $_mountables = [];

    protected $_plugins = [];

    protected $_built = false;


    public function __construct($embedMode = null)
    {
        if($embedMode !== null){
            $this->_embedMode = $embedMode;
        }

        // Add default scalar types
        $this
            ->scalar(new DateScalarType())
            ->scalar(new DateTimeScalarType())
            ->scalar(new TimeScalarType())
            ->scalar(new JsonScalarType())
            ->scalar(new AnyScalarType());
    }

    public function plugin(PluginInterface $plugin){

        $this->_plugins[] = $plugin;
        $plugin->setSchema($this);

        return $this;
    }

    public function getPlugins()
    {
        return $this->_plugins;
    }

    public function embedMode($embedMode){

        $this->_embedMode = $embedMode;
        return $this;
    }

    public function embedList()
    {
        $this->_embedMode = Schema::EMBED_MODE_LIST;
        return $this;
    }

    public function embedRelay()
    {
        $this->_embedMode = Schema::EMBED_MODE_RELAY;
        return $this;
    }

    public function getEmbedMode()
    {
        return $this->_embedMode;
    }

    public function enum(EnumType $enumType)
    {
        $this->_enumTypes[] = $enumType;
        $this->_enumTypesByName[$enumType->getName()] = $enumType;

        $this->_typesByName[$enumType->getName()] = $enumType;

        return $this;
    }

    public function hasEnum($name)
    {
        return isset($this->_enumTypesByName[$name]);
    }

    public function getEnumTypes()
    {
        return $this->_enumTypes;
    }

    public function resolveEnumValue($type, $enumName){

        /** @var EnumType $enumType */
        $enumType = $this->findType($type);

        $values = $enumType->getValues();

        /** @var EnumTypeValue $value */
        foreach($values as $value){

            if($value->getName() == $enumName){
                return $value->getValue();
            }
        }

        return null;
    }

    public function resolveEnumName($type, $enumValue){

        /** @var EnumType $enumType */
        $enumType = $this->findType($type);

        $values = $enumType->getValues();

        /** @var EnumTypeValue $value */
        foreach($values as $value){

            if($value->getValue() == $enumValue){
                return $value->getName();
            }
        }

        return null;
    }

    public function scalar(ScalarType $type)
    {
        $this->_scalarTypes[] = $type;
        $this->_typesByName[$type->name] = $type;

        return $this;
    }

    public function getScalarTypes()
    {
        return $this->_scalarTypes;
    }


    public function object(ObjectType $objectType)
    {
        $this->_objectTypes[] = $objectType;
        $this->_objectTypesByName[$objectType->getName()] = $objectType;

        $this->_typesByName[$objectType->getName()] = $objectType;

        return $this;
    }

    public function getObjectTypes()
    {
        return $this->_objectTypes;
    }

    public function findObjectType($name)
    {
        return array_key_exists($name, $this->_objectTypesByName) ? $this->_objectTypesByName[$name] : null;
    }


    public function union(UnionType $unionType)
    {
        $this->_unionTypes[] = $unionType;
        $this->_unionTypesByName[$unionType->getName()] = $unionType;

        $this->_typesByName[$unionType->getName()] = $unionType;

        return $this;
    }

    public function getUnionTypes()
    {
        return $this->_unionTypes;
    }

    public function findUnionType($name)
    {
        return array_key_exists($name, $this->_unionTypesByName) ? $this->_unionTypesByName[$name] : null;
    }


    public function inputObject(InputObjectType $objectType)
    {
        $this->_inputObjectTypes[] = $objectType;
        $this->_typesByName[$objectType->getName()] = $objectType;

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

    public function findType($name)
    {
        return array_key_exists($name, $this->_typesByName) ? $this->_typesByName[$name] : null;
    }

    public function hasType($name)
    {
        return array_key_exists($name, $this->_typesByName);
    }

    public function mount(SchemaMountableInterface $mountable) {

        $this->_mountables[] = $mountable;
        return $this;
    }

    public function getMountables()
    {
        return $this->_mountables;
    }

    public function build(DiInterface $di, $buildTypes=true){

        if($this->_built){
            return;
        }

        /** @var PluginInterface $plugin */
        foreach($this->_plugins as $plugin){
            $plugin->beforeBuildSchema($this, $di);
        }


        /*** MOUNTABLES ***/

        /** @var SchemaMountableInterface $mountable */
        foreach($this->_mountables as $mountable){

            $mountable->build($this, $di);

            foreach($mountable->getEnumTypes() as $enumType){
                $this->enum($enumType);
            }

            foreach($mountable->getObjectTypes() as $objectType){
                $this->object($objectType);
            }

            foreach($mountable->getUnionTypes() as $unionType){
                $this->union($unionType);
            }

            foreach($mountable->getInputObjectTypes() as $inputObjectType){
                $this->inputObject($inputObjectType);
            }

            foreach($mountable->getObjectTypeGroups() as $objectTypeGroup){
                $this->objectGroup($objectTypeGroup);
            }
        }


        /*** OBJECT TYPE GROUPS ***/

        /** @var ObjectTypeGroupInterface $objectTypeGroup */
        foreach($this->_objectTypeGroups as $objectTypeGroup){

            $objectTypeGroup->build($this, $di);

            foreach($objectTypeGroup->getObjectTypes() as $objectType){
                $this->object($objectType);
            }
        }


        /*** MOUNTABLES ***/

        /** @var SchemaMountableInterface $mountable */
        foreach($this->_mountables as $mountable){

            foreach($mountable->getFieldGroups() as $objectName => $fieldGroups){

                $objectType = $this->findObjectType($objectName);
                if($objectType){

                    foreach($fieldGroups as $group){
                        $objectType->fieldGroup($group);
                    }
                }
            }

            foreach($mountable->getFields() as $objectName => $fields){

                $objectType = $this->findObjectType($objectName);
                if($objectType){

                    foreach($fields as $field){
                        $objectType->field($field);
                    }
                }
            }
        }


        /*** FIELD GROUPS ***/

        /** @var ObjectType $objectType */
        foreach($this->_objectTypes as $objectType){

            /** @var FieldGroupInterface $fieldGroup */
            foreach($objectType->getFieldGroups() as $fieldGroup){

                $fieldGroup->build($this, $di);

                foreach($fieldGroup->getFields() as $field){
                    $objectType->field($field);
                }
            }
        }

        if($buildTypes) {

            /*** OBJECT TYPES ***/

            /** @var ObjectType $objectType */
            foreach ($this->_objectTypes as $objectType) {
                $objectType->build($this, $di);
            }

            /*** UNION TYPES ***/

            /** @var UnionType $unionType */
            foreach ($this->_unionTypes as $unionType) {
                $unionType->build($this, $di);
            }

            /*** FIELDS ***/
            /** @var ObjectType $objectType */
            foreach ($this->_objectTypes as $objectType) {

                /** @var Field $field */
                foreach ($objectType->getFields() as $field) {
                    $field->build($this, $objectType, $di);
                }
            }

            /*** INPUT OBJECT TYPES ***/

            /** @var InputObjectType $inputObjectType */
            foreach ($this->_inputObjectTypes as $objectType) {
                $objectType->build($this, $di);
            }
        }


        /** @var PluginInterface $plugin */
        foreach($this->_plugins as $plugin){
            $plugin->afterBuildSchema($this, $di);
        }

        $this->_built = true;
    }


    public function getAclResources()
    {
        $response = [];

        /** @var ObjectType $objectType */
        foreach($this->_objectTypes as $objectType){

            $fieldNames = [];

            /** @var Field $field */
            foreach($objectType->getFields() as $field){
                $fieldNames[] = $field->getName();
            }

            $response[] = [$objectType->getName(), $fieldNames];
        }

        return $response;
    }

    public function getAclRules(array $roles)
    {
        $allowItems = [];
        $denyItems = [];

        /** @var ObjectType $objectType */
        foreach($this->_objectTypes as $objectType){

            $objectTypeName = $objectType->getName();

            /** @var Field $field */
            foreach($objectType->getFields() as $field){

                $fieldName = $field->getName();

                foreach($field->getAllowedRoles() as $role){
                    $allowItems[] = [$role, $objectTypeName, $fieldName];
                }

                foreach($field->getDeniedRoles() as $role){
                    $denyItems[] = [$role, $objectTypeName, $fieldName];
                }
            }
        }

        return [
            Acl\Enum::ALLOW => $allowItems,
            Acl\Enum::DENY => $denyItems
        ];
    }


    public static function factory($embedMode = null)
    {
        return new static($embedMode);
    }
}
