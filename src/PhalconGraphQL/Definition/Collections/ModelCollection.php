<?php

namespace PhalconGraphQL\Definition\Collections;

use Phalcon\DiInterface;
use PhalconGraphQL\Definition\Fields\AllModelField;
use PhalconGraphQL\Definition\Fields\CreateModelField;
use PhalconGraphQL\Definition\Fields\DeleteModelField;
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


    public function all($objectType=Types::QUERY, $name=null, $description=null)
    {
        $field = AllModelField::factory($this->_modelClass, $name, null, $description);
        $this->configureAllField($field);

        $this->field($objectType, $field);
        $this->_queryFields[] = $field;

        return $this;
    }

    public function find($objectType=Types::QUERY, $name=null, $description=null)
    {
        $field = FindModelField::factory($this->_modelClass, $name, null, $description);
        $this->configureFindField($field);

        $this->field($objectType, $field);
        $this->_queryFields[] = $field;

        return $this;
    }

    public function create($objectType=Types::MUTATION, $name=null, $returnType=null, $description=null)
    {
        $inputObject = ModelInputObjectType::create($this->_modelClass);
        $this->configureCreateInputObjectType($inputObject);

        $this->inputObject($inputObject);

        $field = CreateModelField::factory($this->_modelClass, $name, $returnType, null, $description);
        $this->configureCreateField($field);

        $this->field($objectType, $field);
        $this->_mutationFields[] = $field;

        return $this;
    }

    public function update($objectType=Types::MUTATION, $name=null, $returnType=null, $description=null)
    {
        $inputObject = ModelInputObjectType::update($this->_modelClass);
        $this->configureUpdateInputObjectType($inputObject);

        $this->inputObject($inputObject);

        $field = UpdateModelField::factory($this->_modelClass, $name, $returnType, null, $description);
        $this->configureUpdateField($field);

        $this->field($objectType, $field);
        $this->_mutationFields[] = $field;

        return $this;
    }

    public function delete($objectType=Types::MUTATION, $name=null, $description=null)
    {
        $field = DeleteModelField::factory($this->_modelClass, $name, null, $description);
        $this->configureDeleteField($field);

        $this->field($objectType, $field);
        $this->_mutationFields[] = $field;

        return $this;
    }

    public function crud($queryObjectType = Types::QUERY, $mutationObjectType = Types::MUTATION)
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

    protected function configureCreateField(CreateModelField $field){}
    protected function configureUpdateField(UpdateModelField $field){}
    protected function configureDeleteField(DeleteModelField $field){}

    protected function configureCreateInputObjectType(ModelInputObjectType $objectType){}
    protected function configureUpdateInputObjectType(ModelInputObjectType $objectType){}


    public function build(Schema $schema, DiInterface $di)
    {
        if($this->_handler){
            $this->_modelObjectType->handler($this->_handler);
        }

        if($this->_queryHandler){

            /** @var ModelField $field */
            foreach($this->_queryFields as $field){
                $field->handler($this->_queryHandler);
            }
        }

        if($this->_mutationHandler){

            /** @var ModelField $field */
            foreach($this->_mutationFields as $field){
                $field->handler($this->_mutationHandler);
            }
        }
    }


    public static function factory($modelClass=null)
    {
        return new ModelCollection($modelClass);
    }
}