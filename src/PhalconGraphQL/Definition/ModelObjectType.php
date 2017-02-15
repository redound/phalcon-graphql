<?php

namespace PhalconGraphQL\Definition;

use Phalcon\DiInterface;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Mvc\Model\RelationInterface;
use PhalconGraphQL\Constants\Services;
use PhalconGraphQL\Core;
use PhalconGraphQL\Definition\FieldGroups\FieldGroupInterface;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\ModelField;
use PhalconGraphQL\Definition\Fields\RelationModelField;
use PhalconGraphQL\Plugins\Plugin;

class ModelObjectType extends ObjectType
{
    protected $_modelClass;

    protected $_excludedFields = [];
    protected $_excludeRelations = false;
    protected $_relationEmbedMode;

    public function __construct($modelClass, $name=null, $description=null)
    {
        // Use class name if name not provided
        if($name === null) {
            $name = Core::getShortClass($modelClass);
        }

        parent::__construct($name, $description);

        $this->_modelClass = $modelClass;
    }

    public function getModel()
    {
        return $this->_modelClass;
    }

    public function exclude($field)
    {
        return $this->removeField($field);
    }

    public function excludeRelations($excludeRelations = true)
    {
        $this->_excludeRelations = $excludeRelations;
        return $this;
    }

    public function embedRelationsList()
    {
        $this->_relationEmbedMode = Schema::EMBED_MODE_LIST;
        return $this;
    }

    public function embedRelationsRelay()
    {
        $this->_relationEmbedMode = Schema::EMBED_MODE_RELAY;
        return $this;
    }

    public function getRelationEmbedMode()
    {
        return $this->_relationEmbedMode;
    }

    public function relationEmbedMode($embedMode)
    {
        $this->_relationEmbedMode = $embedMode;
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

        /** @var Plugin $plugin */
        foreach($this->_plugins as $plugin){
            $plugin->setSchema($schema);
        }

        $this->executeBeforeBuildPlugins($schema, $di);

        $relationEmbedMode = $this->_relationEmbedMode;

        if($relationEmbedMode === null){
            $relationEmbedMode = $schema->getEmbedMode();
        }

        /** @var MetaData $modelsMetadata */
        $modelsMetadata = $di->get(Services::MODELS_METADATA);

        /** @var Manager $modelsManager */
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

        if(method_exists($model, 'excludedOutputFields')){
            $skip = array_merge($skip, $model->excludedOutputFields());
        }

        if(method_exists($model, 'typeMap')){
            $typeMap = $model->typeMap();
        }

        $mappedDataTypes = [];
        $mappedNonNullAttributes = [];

        foreach ($dataTypes as $attributeName => $dataType) {

            $mappedAttributeName = is_array($columnMap) && array_key_exists($attributeName, $columnMap) ? $columnMap[$attributeName] : $attributeName;

            $type = null;
            if($attributeName == $identityField){
                $type = Types::ID;
            }
            else if(array_key_exists($mappedAttributeName, $typeMap)){
                $type = $typeMap[$mappedAttributeName];
            }
            else {
                $type = Types::getMappedDatabaseType($dataType);
            }

            $mappedDataTypes[$mappedAttributeName] = $type;

            if(in_array($attributeName, $nonNullAttributes)){
                $mappedNonNullAttributes[] = $mappedAttributeName;
            }
        }

        foreach($mappedDataTypes as $attribute => $type){

            if(in_array($attribute, $skip) || $this->fieldExists($attribute)){
                continue;
            }

            $field = Field::factory($attribute, $type);
            if(in_array($attribute, $mappedNonNullAttributes)){
                $field->nonNull();
            }

            $newFields[] = $field;
        }

        // Relations
        if(!$this->_excludeRelations) {

            /** @var RelationInterface $relation */
            foreach ($modelsManager->getRelations($modelClass) as $relation) {

                $referencedModelClass = Core::getShortClass($relation->getReferencedModel());

                $options = $relation->getOptions();
                $relationName = is_array($options) && array_key_exists('alias',
                    $options) ? $options['alias'] : $referencedModelClass;
                $isList = in_array($relation->getType(), [Model\Relation::HAS_MANY, Model\Relation::HAS_MANY_THROUGH]);

                $field = RelationModelField::factory($relation->getReferencedModel(), lcfirst($relationName), $referencedModelClass)
                    ->isList($isList)
                    ->embedMode($relationEmbedMode);

                $newFields[] = $field;
            }
        }

        $this->_fields = array_merge($newFields, $originalFields);

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

    /**
     * @return static
     */
    public static function factory($modelClass, $name=null, $description=null)
    {
        return new ModelObjectType($modelClass, $name, $description);
    }
}