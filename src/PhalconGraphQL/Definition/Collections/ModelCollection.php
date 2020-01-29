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

    protected $_mutationHandler = null;
    protected $_queryHandler = null;
    protected $_handler = null;

    protected $_queryFields = [];
    protected $_mutationFields = [];
    protected $_modelObjectType;
    protected $_modelObjectTypeGroup;

    protected $_allowedQueryRoles = [];
    protected $_deniedQueryRoles = [];

    protected $_allowedMutationRoles = [];
    protected $_deniedMutationRoles = [];

    protected $_allowedModelObjectRoles = [];
    protected $_deniedModelObjectRoles = [];

    public function __construct($modelClass=null)
    {
        $this->_modelClass = $modelClass;

        parent::__construct();

        if($this->_modelClass) {

            $objectType = ModelObjectType::factory($this->_modelClass);
            $this->configureObjectType($objectType);

            $embeddedGroup = EmbeddedObjectTypeGroup::factory($objectType);
            $this->configureEmbeddedObjectTypeGroup($embeddedGroup);

            $this->objectGroup($embeddedGroup);
            $this->_modelObjectType = $objectType;

            $this->_modelObjectTypeGroup = $embeddedGroup;
        }
    }

    public function model($modelClass){

        $this->_modelClass = $modelClass;
        return $this;
    }

    public function mutationHandler($mutationHandler)
    {
        $this->_mutationHandler = $mutationHandler;
        return $this;
    }

    public function queryHandler($queryHandler)
    {
        $this->_queryHandler = $queryHandler;
        return $this;
    }

    public function handler($handler)
    {
        $this->_handler = $handler;
        return $this;
    }

    public function allowQuery($roles)
    {
        $this->_allowedQueryRoles = array_merge($this->_allowedQueryRoles, is_array($roles) ? $roles : [$roles]);
        return $this;
    }

    public function denyQuery($roles)
    {
        $this->_deniedQueryRoles = array_merge($this->_deniedQueryRoles, is_array($roles) ? $roles : [$roles]);
        return $this;
    }

    public function allowMutation($roles)
    {
        $this->_allowedMutationRoles = array_merge($this->_allowedMutationRoles, is_array($roles) ? $roles : [$roles]);
        return $this;
    }

    public function denyMutation($roles)
    {
        $this->_deniedMutationRoles = array_merge($this->_deniedMutationRoles, is_array($roles) ? $roles : [$roles]);
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


    public function queryField(Field $field, $objectType=Types::VIEWER)
    {
        $this->field($objectType, $field);
        $this->_queryFields[] = $field;

        return $this;
    }

    public function mutationField(Field $field, $objectType=Types::MUTATION)
    {
        $this->field($objectType, $field);
        $this->_mutationFields[] = $field;

        return $this;
    }


    public function all($objectType=Types::VIEWER, $name=null)
    {
        $field = AllModelField::factory($this->_modelClass, $name);

        $this->configureQueryField($field);
        $this->configureAllField($field);

        $this->queryField($field, $objectType);

        return $this;
    }

    public function find($objectType=Types::VIEWER, $name=null)
    {
        $field = FindModelField::factory($this->_modelClass, $name);

        $this->configureQueryField($field);
        $this->configureFindField($field);

        $this->queryField($field, $objectType);

        return $this;
    }

    public function create($objectType=Types::MUTATION, $name=null, $returnType=null)
    {
        $inputObject = ModelInputObjectType::create($this->_modelClass);
        $this->configureCreateInputObjectType($inputObject);

        $this->inputObject($inputObject);

        $field = new CreateModelField($this->_modelClass, $name, $returnType);

        $this->configureMutationField($field);
        $this->configureCreateField($field);

        $this->mutationField($field, $objectType);

        return $this;
    }

    public function update($objectType=Types::MUTATION, $name=null, $returnType=null)
    {
        $inputObject = ModelInputObjectType::update($this->_modelClass);
        $this->configureUpdateInputObjectType($inputObject);

        $this->inputObject($inputObject);

        $field = new UpdateModelField($this->_modelClass, $name, $returnType);

        $this->configureMutationField($field);
        $this->configureUpdateField($field);

        $this->mutationField($field, $objectType);

        return $this;
    }

    public function delete($objectType=Types::MUTATION, $name=null)
    {
        $field = new DeleteModelField($this->_modelClass, $name);

        $this->configureMutationField($field);
        $this->configureDeleteField($field);

        $this->mutationField($field, $objectType);

        return $this;
    }

    public function crud($queryObjectType = Types::VIEWER, $mutationObjectType = Types::MUTATION)
    {
        $this
            ->all($queryObjectType)
            ->find($queryObjectType)

            ->create($mutationObjectType)
            ->update($mutationObjectType)
            ->delete($mutationObjectType);

        return $this;

    }


    protected function configureEmbeddedObjectTypeGroup(EmbeddedObjectTypeGroup $group){}
    protected function configureObjectType(ModelObjectType $objectType){}

    protected function configureAllField(AllModelField $field){}
    protected function configureFindField(FindModelField $field){}
    protected function configureQueryField(ModelField $field){}

    protected function configureCreateField(CreateModelField $field){}
    protected function configureUpdateField(UpdateModelField $field){}
    protected function configureDeleteField(DeleteModelField $field){}
    protected function configureMutationField(ModelField $field){}

    protected function configureCreateInputObjectType(ModelInputObjectType $objectType){}
    protected function configureUpdateInputObjectType(ModelInputObjectType $objectType){}


    public function build(Schema $schema, DiInterface $di)
    {
        if($this->_handler){
            $this->_modelObjectType->handler($this->_handler);
        }

        /** @var ModelField $field */
        foreach($this->_queryFields as $field){

            if($this->_queryHandler) {
                $field->handler($this->_queryHandler);
            }

            $field->allow($this->_allowedQueryRoles);
            $field->deny($this->_deniedQueryRoles);
        }

        /** @var ModelField $field */
        foreach($this->_mutationFields as $field){

            if($this->_mutationHandler) {
                $field->handler($this->_mutationHandler);
            }

            $field->allow($this->_allowedMutationRoles);
            $field->deny($this->_deniedMutationRoles);
        }

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