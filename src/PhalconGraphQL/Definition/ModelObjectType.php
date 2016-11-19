<?php

namespace PhalconGraphQL\Definition;

use Phalcon\Db\Column;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Mvc\Model\RelationInterface;
use PhalconGraphQL\Constants\Services;
use PhalconGraphQL\Core;

class ModelObjectType extends ObjectType
{
    protected $_modelClass;

    protected $_excludedFields = [];
    protected $_excludeRelations = false;
    protected $_relationEmbedMode = null;

    public function __construct($modelClass, $name=null, $description=null)
    {
        // Use class name if name not provided
        if($name === null) {
            $name = Core::getShortClass($modelClass);
        }

        parent::__construct($name, $description);

        $this->_modelClass = $modelClass;
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

    public function embedRelationsOnlyEdges()
    {
        $this->_relationEmbedMode = Schema::EMBED_MODE_EDGES;
        return $this;
    }

    public function embedRelationsOnlyNode()
    {
        $this->_relationEmbedMode = Schema::EMBED_MODE_NODE;
        return $this;
    }

    public function embedRelations()
    {
        $this->_relationEmbedMode = Schema::EMBED_MODE_ALL;
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

        if($this->_relationEmbedMode === null){
            $this->_relationEmbedMode = $schema->getEmbedMode();
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
                $type = $this->getMappedDatabaseType($dataType);
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

                $field = ModelField::factory($relation->getReferencedModel(), lcfirst($relationName), $referencedModelClass)
                    ->isList($isList)
                    ->embedMode($this->_relationEmbedMode);

                $newFields[] = $field;
            }
        }

        $this->_fields = array_merge($newFields, $originalFields);

        parent::build($schema, $di);

        $this->_built = true;
    }

    protected function getMappedDatabaseType($type)
    {
        $responseType = null;

        switch ($type) {

            case Column::TYPE_INTEGER:
            case Column::TYPE_BIGINTEGER: {

                $responseType = Types::INT;
                break;
            }

            case Column::TYPE_DECIMAL:
            case Column::TYPE_DOUBLE:
            case Column::TYPE_FLOAT: {

                $responseType = Types::FLOAT;
                break;
            }

            case Column::TYPE_BOOLEAN: {

                $responseType = Types::BOOLEAN;
                break;
            }

            case Column::TYPE_VARCHAR:
            case Column::TYPE_CHAR:
            case Column::TYPE_TEXT:
            case Column::TYPE_BLOB:
            case Column::TYPE_MEDIUMBLOB:
            case Column::TYPE_LONGBLOB: {

                $responseType = Types::STRING;
                break;
            }

            // TODO: Implement?
//            case Column::TYPE_DATE:
//            case Column::TYPE_DATETIME: {
//
//                $responseType = self::TYPE_DATE;
//                break;
//            }

            default:
                $responseType = Types::STRING;
        }

        return $responseType;
    }

    /**
     * @return static
     */
    public static function factory($modelClass, $name=null, $description=null)
    {
        return new ModelObjectType($modelClass, $name, $description);
    }
}