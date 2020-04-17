<?php

namespace PhalconGraphQL\Definition\Collections;

use Phalcon\Di\DiInterface;
use PhalconGraphQL\Definition\Fields\AllModelField;
use PhalconGraphQL\Definition\Fields\CreateModelField;
use PhalconGraphQL\Definition\Fields\DeleteModelField;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\FindModelField;
use PhalconGraphQL\Definition\Fields\ModelField;
use PhalconGraphQL\Definition\Fields\UpdateModelField;
use PhalconGraphQL\Definition\ModelInputObjectType;
use PhalconGraphQL\Definition\ModelObjectType;
use PhalconGraphQL\Definition\ObjectTypeGroups\EmbeddedObjectTypeGroup;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\Types;

class ModelCollection extends Collection
{
    protected $_modelClass;

    protected $_handler = null;

    protected $_modelObjectType;
    protected $_modelObjectTypeGroup;

    protected $_allowedModelObjectRoles = [];
    protected $_deniedModelObjectRoles = [];

    private $_objectConfigurator;

    public function __construct($modelClass=null)
    {
        $this->_modelClass = $modelClass;

        parent::__construct();

        if($this->_modelClass) {

            $objectType = ModelObjectType::factory($this->_modelClass);
            if($this->_objectConfigurator) {

                $configurator = $this->_objectConfigurator;
                $configurator($objectType);

                // Set to null to prevent serialization
                $this->_objectConfigurator = null;
            }

            $this->configureObjectType($objectType);

            $embeddedGroup = EmbeddedObjectTypeGroup::factory($objectType);
            $this->configureEmbeddedObjectTypeGroup($embeddedGroup);

            $this->objectGroup($embeddedGroup);
            $this->_modelObjectType = $objectType;

            $this->_modelObjectTypeGroup = $embeddedGroup;
        }
    }

    public function model($modelClass, $objectConfigurator=null){

        $this->_modelClass = $modelClass;
        $this->_objectConfigurator = $objectConfigurator;

        return $this;
    }

    public function handler($handler)
    {
        $this->_handler = $handler;
        return $this;
    }



    public function allowModelObject($roles)
    {
        $this->_allowedModelObjectRoles = array_merge($this->_allowedModelObjectRoles, is_array($roles) ? $roles : [$roles]);
        return $this;
    }

    public function denyModelObject($roles)
    {
        $this->_deniedModelObjectRoles = array_merge($this->_deniedModelObjectRoles, is_array($roles) ? $roles : [$roles]);
        return $this;
    }


    public function all(callable $configurator=null)
    {
        $field = AllModelField::factory($this->_modelClass);
        if($configurator){
            $configurator($field);
        }

        $this->configureAllField($field);

        $this->queryField($field);

        return $this;
    }

    public function find(callable $configurator=null)
    {
        $field = FindModelField::factory($this->_modelClass);
        if($configurator){
            $configurator($field);
        }

        $this->configureFindField($field);

        $this->queryField($field);

        return $this;
    }

    public function create(callable $inputConfigurator=null, callable $fieldConfigurator=null)
    {
        $inputObject = ModelInputObjectType::create($this->_modelClass);
        if($inputConfigurator){
            $inputConfigurator($inputObject);
        }

        $this->configureCreateInputObjectType($inputObject);
        $this->inputObject($inputObject);

        $field = new CreateModelField($this->_modelClass);
        if($fieldConfigurator){
            $fieldConfigurator($field);
        }

        $this->configureCreateField($field);

        $this->mutationField($field);

        return $this;
    }

    public function update(callable $inputConfigurator=null, callable $fieldConfigurator=null)
    {
        $inputObject = ModelInputObjectType::update($this->_modelClass);
        if($inputConfigurator){
            $inputConfigurator($inputObject);
        }

        $this->configureUpdateInputObjectType($inputObject);
        $this->inputObject($inputObject);

        $field = new UpdateModelField($this->_modelClass);
        if($fieldConfigurator){
            $fieldConfigurator($field);
        }

        $this->configureUpdateField($field);

        $this->mutationField($field);

        return $this;
    }

    public function delete(callable $configurator=null)
    {
        $field = new DeleteModelField($this->_modelClass);
        if($configurator){
            $configurator($field);
        }

        $this->configureDeleteField($field);

        $this->mutationField($field);

        return $this;
    }

    public function crud()
    {
        $this
            ->all()
            ->find()

            ->create()
            ->update()
            ->delete();

        return $this;
    }


    protected function configureEmbeddedObjectTypeGroup(EmbeddedObjectTypeGroup $group){}
    protected function configureObjectType(ModelObjectType $objectType){}

    protected function configureAllField(AllModelField $field){}
    protected function configureFindField(FindModelField $field){}

    protected function configureCreateField(CreateModelField $field){}
    protected function configureUpdateField(UpdateModelField $field){}
    protected function configureDeleteField(DeleteModelField $field){}

    protected function configureCreateInputObjectType(ModelInputObjectType $objectType){}
    protected function configureUpdateInputObjectType(ModelInputObjectType $objectType){}


    public function build(Schema $schema, DiInterface $di)
    {
        $this->_modelObjectTypeGroup->allow($this->_allowedQueryRoles);
        $this->_modelObjectTypeGroup->allow($this->_allowedMutationRoles);

        $this->_modelObjectTypeGroup->allow($this->_allowedModelObjectRoles);
        $this->_modelObjectTypeGroup->deny($this->_deniedModelObjectRoles);

        parent::build($schema, $di);
    }


    public static function factory($modelClass=null)
    {
        return new static($modelClass);
    }
}