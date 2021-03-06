<?php

namespace PhalconGraphQL\Definition;

use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\Model\MetaData;
use PhalconGraphQL\Constants\Services;
use PhalconGraphQL\Core;

class ModelInputObjectType extends InputObjectType
{
    const TYPE_CREATE = 'create';
    const TYPE_UPDATE = 'update';

    protected $_modelClass;

    protected $_excludedFields = [];

    protected $_excludeIdentity = false;
    protected $_fieldsOptional = false;

    public function __construct($modelClass, $name=null)
    {
        // Use class name if name not provided
        if($name === null) {
            $this->_name = Types::addInput(Core::getShortClass($modelClass));
        }

        parent::__construct($name);

        $this->_modelClass = $modelClass;
    }

    public function exclude($field)
    {
        return $this->removeField($field);
    }

    public function excludeIdentity($excludeIdentity = true)
    {
        $this->_excludeIdentity = $excludeIdentity;
        return $this;
    }

    public function fieldsOptional($fieldsOptional = true)
    {
        $this->_fieldsOptional = $fieldsOptional;
        return $this;
    }

    public function removeField($fieldName)
    {
        $this->_excludedFields[] = $fieldName;

        return parent::removeField($fieldName);
    }

    public function build(Schema $schema, DiInterface $di)
    {
        if($this->_built){
            return;
        }

        /** @var MetaData $modelsMetadata */
        $modelsMetadata = $di->get(Services::MODELS_METADATA);

        /** @var ManagerInterface $modelsManager */
        $modelsManager = $di->get(Services::MODELS_MANAGER);

        $modelClass = $this->_modelClass;
        $model = new $modelClass();

        $originalFields = $this->_fields;
        $newFields = [];

        // Attributes
        $columnMap = $modelsMetadata->getColumnMap($model);
        $dataTypes = $modelsMetadata->getDataTypes($model);
        $nonNullAttributes = $modelsMetadata->getNotNullAttributes($model);
        $identityField = $modelsMetadata->getIdentityField($model);

        $skip = $this->_excludedFields;
        $typeMap = [];

        if(method_exists($model, 'excludedFields')){
            $skip = array_merge($skip, $model->excludedFields());
        }

        if(method_exists($model, 'excludedInputFields')){
            $skip = array_merge($skip, $model->excludedInputFields());
        }

        if(method_exists($model, 'typeMap')){
            $typeMap = $model->typeMap();
        }

        $relationFields = [];

        /** @var RelationInterface $relation */
        foreach ($modelsManager->getRelations($modelClass) as $relation) {

            $fieldsRaw = $relation->getFields();
            $fields = is_array($fieldsRaw) ? $fieldsRaw : [$fieldsRaw];

            $relationFields = array_merge($relationFields, $fields);
        }

        $mappedDataTypes = [];
        $mappedNonNullAttributes = [];

        foreach ($dataTypes as $attributeName => $dataType) {

            if($attributeName == $identityField && $this->_excludeIdentity){
                continue;
            }

            $mappedAttributeName = is_array($columnMap) && array_key_exists($attributeName, $columnMap) ? $columnMap[$attributeName] : $attributeName;

            $type = null;
            if(array_key_exists($mappedAttributeName, $typeMap)){
                $type = $typeMap[$mappedAttributeName];
            }
            else if($attributeName == $identityField || in_array($mappedAttributeName, $relationFields)){
                $type = Types::ID;
            }
            else {
                $type = Types::getMappedDatabaseType($dataType);
            }

            $mappedDataTypes[$mappedAttributeName] = $type;

            if((!$this->_fieldsOptional && in_array($attributeName, $nonNullAttributes)) || $attributeName == $identityField){
                $mappedNonNullAttributes[] = $mappedAttributeName;
            }
        }

        foreach($mappedDataTypes as $attribute => $type){

            if(in_array($attribute, $skip) || $this->fieldExists($attribute)){
                continue;
            }

            $field = InputField::factory($attribute, $type);
            if(in_array($attribute, $mappedNonNullAttributes)){
                $field->nonNull();
            }

            $newFields[] = $field;
        }

        $this->_fields = array_merge($newFields, $originalFields);

        parent::build($schema, $di);

        $this->_built = true;
    }

    /**
     * @return static
     */
    public static function factory($modelClass)
    {
        return new static($modelClass);
    }

    public static function create($modelClass){

        $name = Types::addCreateInput(Core::getShortClass($modelClass));

        $result = new static($modelClass, $name);
        $result->excludeIdentity();

        return $result;
    }

    public static function update($modelClass){

        $name = Types::addUpdateInput(Core::getShortClass($modelClass));

        $result = new static($modelClass, $name);
        $result->fieldsOptional();

        return $result;
    }
}